import type {
  AuthUserResponse,
  LoginPayload,
  LoginResponse,
  LogoutResponse,
} from "@/types/auth.types";

import { api } from "@/api/client";

export async function login(
  payload: LoginPayload,
): Promise<LoginResponse> {
  const response = await api.post<LoginResponse>(
    "/login",
    payload,
  );

  return response.data;
}

export async function getAuthenticatedUser(): Promise<AuthUserResponse> {
  const response = await api.get<AuthUserResponse>(
    "/admin/me",
  );

  return response.data;
}

export async function logout(): Promise<LogoutResponse> {
  const response = await api.post<LogoutResponse>(
    "/logout",
  );

  return response.data;
}