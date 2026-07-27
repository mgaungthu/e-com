export type AdminUser = {
  id: number;
  name: string;
  email: string;
  avatar_path: string | null;
  roles: string[];
  permissions: string[];
};

export type AuthUserResponse = {
  success: boolean;
  data: {
    user: AdminUser;
  };
};

export type LoginPayload = {
  email: string;
  password: string;
  remember?: boolean;
};

export type LoginResponse = {
  success: boolean;
  message: string;
  data: {
    user: AdminUser;
  };
};

export type LogoutResponse = {
  success: boolean;
  message: string;
};