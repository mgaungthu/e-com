<?php

namespace Tests\Feature\Api\V1;

use App\Models\Conversation;
use App\Models\Feed;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    public function test_chat_requires_authentication(): void
    {
        $this->getJson('/api/v1/chat/conversation')
            ->assertUnauthorized();

        $this->getJson('/api/v1/chat/conversation/messages')
            ->assertUnauthorized();

        $this->postJson('/api/v1/chat/conversation/messages', [
            'type' => 'text',
            'message' => 'Hello',
        ])->assertUnauthorized();

        $this->postJson('/api/v1/chat/conversation/read')
            ->assertUnauthorized();
    }

    public function test_it_creates_a_conversation_for_the_customer(): void
    {
        $user = $this->verifiedUser();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/chat/conversation');

        $response
            ->assertOk()
            ->assertJsonPath('data.conversation.status', 'open')
            ->assertJsonPath('data.conversation.unread_count', 0);

        $this->assertDatabaseHas('conversations', [
            'user_id' => $user->id,
            'status' => 'open',
        ], );

        $this->assertDatabaseCount('conversations', 1);

        /*
         * Requesting the conversation again must reuse
         * the existing conversation.
         */
        $this->getJson('/api/v1/chat/conversation')->assertOk();

        $this->assertDatabaseCount('conversations', 1);
    }

    public function test_customer_can_send_a_text_message(): void
    {
        $user = $this->verifiedUser();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/chat/conversation/messages', [
            'type' => 'text',
            'message' => 'Hello Burmese Shave Club',
        ], );

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.message.type', 'text')
            ->assertJsonPath('data.message.message', 'Hello Burmese Shave Club')
            ->assertJsonPath('data.message.sender.type', 'customer')
            ->assertJsonPath('data.message.sender.id', $user->id);

        $conversation =
            Conversation::query()
                ->where('user_id', $user->id)
                ->firstOrFail();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,

            'sender_type' => 'customer',

            'sender_id' => $user->id,

            'type' => 'text',

            'message' => 'Hello Burmese Shave Club',
        ], );

        $conversation->refresh();

        $this->assertNotNull($conversation->last_message_at);

        $this->assertNotNull($conversation->user_last_read_at);
    }

    public function test_text_message_requires_message_content(): void
    {
        $user = $this->verifiedUser();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/chat/conversation/messages', [
            'type' => 'text',
            'message' => '',
        ], )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'message',
            ]);

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_customer_only_receives_messages_from_their_conversation(): void
    {
        $user = $this->verifiedUser();

        $otherUser =
            $this->verifiedUser();

        $conversation =
            Conversation::query()->create([
                'user_id' => $user->id,
                'status' => 'open',
            ]);

        $otherConversation =
            Conversation::query()->create([
                'user_id' => $otherUser->id,
                'status' => 'open',
            ]);

        Message::query()->create([
            'conversation_id' => $conversation->id,

            'sender_type' => 'customer',

            'sender_id' => $user->id,

            'type' => 'text',

            'message' => 'My message',
        ]);

        Message::query()->create([
            'conversation_id' => $otherConversation->id,

            'sender_type' => 'customer',

            'sender_id' => $otherUser->id,

            'type' => 'text',

            'message' => 'Private other user message',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/chat/conversation/messages');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'My message',
            ])
            ->assertJsonMissing([
                'message' => 'Private other user message',
            ]);
    }

    public function test_messages_are_returned_newest_first(): void
    {
        $user = $this->verifiedUser();

        $conversation =
            Conversation::query()->create([
                'user_id' => $user->id,
                'status' => 'open',
            ]);

        $older =
            Message::query()->create([
                'conversation_id' => $conversation->id,

                'sender_type' => 'customer',

                'sender_id' => $user->id,

                'type' => 'text',

                'message' => 'Older message',

                'created_at' => now()->subMinute(),

                'updated_at' => now()->subMinute(),
            ]);

        $newer =
            Message::query()->create([
                'conversation_id' => $conversation->id,

                'sender_type' => 'admin',

                'sender_id' => $user->id,

                'type' => 'text',

                'message' => 'Newer message',

                'created_at' => now(),

                'updated_at' => now(),
            ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/chat/conversation/messages');

        $response->assertOk();

        $this->assertSame($newer->id, $response->json('data.messages.0.id'));

        $this->assertSame($older->id, $response->json('data.messages.1.id'));
    }

    public function test_customer_can_mark_conversation_as_read(): void
    {
        $user = $this->verifiedUser();

        $conversation =
            Conversation::query()->create([
                'user_id' => $user->id,

                'status' => 'open',

                'user_last_read_at' => now()->subHour(),
            ]);

        Message::query()->create([
            'conversation_id' => $conversation->id,

            'sender_type' => 'admin',

            'sender_id' => $user->id,

            'type' => 'text',

            'message' => 'Support reply',
        ]);

        Sanctum::actingAs($user);

        /*
         * Conversation should have one unread admin message.
         */
        $this->getJson('/api/v1/chat/conversation')
            ->assertOk()
            ->assertJsonPath('data.conversation.unread_count', 1);

        $response =
            $this->postJson('/api/v1/chat/conversation/read');

        $response
            ->assertOk()
            ->assertJsonPath('data.conversation.unread_count', 0);

        $conversation->refresh();

        $this->assertNotNull($conversation->user_last_read_at);
    }

    public function test_customer_can_share_a_feed_to_chat(): void
    {
        $user = $this->verifiedUser();

        $feed = Feed::query()->create([
            'user_id' => $user->id,
            'caption' => 'Shaving setup',
            'status' => 'published',
            'is_active' => true,
            'published_at' => now()->subMinute(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/chat/conversation/messages', [
            'type' => 'feed',
            'feed_id' => $feed->id,
        ], );

        $response
            ->assertCreated()
            ->assertJsonPath('data.message.type', 'feed')
            ->assertJsonPath('data.message.feed.id', $feed->id);

        $this->assertDatabaseHas('messages', [
            'type' => 'feed',
            'feed_id' => $feed->id,
            'sender_id' => $user->id,
            'sender_type' => 'customer',
        ], );
    }

    public function test_feed_message_requires_feed_id(): void
    {
        $user = $this->verifiedUser();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/chat/conversation/messages', [
            'type' => 'feed',
        ], )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'feed_id',
            ]);
    }

    public function test_customer_can_share_a_product_to_chat(): void
    {
        $user = $this->verifiedUser();

        $product = Product::factory()->create([
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/chat/conversation/messages', [
            'type' => 'product',
            'product_id' => $product->id,
        ], );

        $response
            ->assertCreated()
            ->assertJsonPath('data.message.type', 'product')
            ->assertJsonPath('data.message.product.id', $product->id)
            ->assertJsonPath('data.message.product.slug', $product->slug);

        $this->assertDatabaseHas('messages', [
            'type' => 'product',
            'product_id' => $product->id,
            'sender_id' => $user->id,
            'sender_type' => 'customer',
        ], );
    }

    public function test_product_message_requires_product_id(): void
    {
        $user = $this->verifiedUser();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/chat/conversation/messages', [
            'type' => 'product',
        ], )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'product_id',
            ]);
    }
}
