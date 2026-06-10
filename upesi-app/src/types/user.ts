import type { Country } from './country';
import type { MerchantProfile } from './merchantProfile';
import type { Currency, Wallet } from './wallet';
export interface UserStats {
  products_count?: number;
  orders_as_buyer_count?: number;
  orders_as_seller_count?: number;
  merchant_profiles_count?: number;
  service_requests_count?: number;
}
export interface UserStatus {
  is_active: boolean;
  is_approved: boolean | null;
  is_banned: boolean;
  email_verified_at: string | null;
  is_deleted: boolean;
}

export interface UserSecurity {
  has_2fa_enabled: number;
  has_verified_email: boolean;
}

export interface FormattedCurrency {
  with_symbol: string;
  with_code: string;
}
export interface Role {
  id: number | string;
  name: string;
  guard_name: string;
  created_at?: string;
  updated_at?: string;
}

export interface User {
  id: string;
  name: string;
  first_name: string;
  last_name: string;
  full_name: string;
  email: string;
  phone: string;
  prefecture: string | null;
  profile_photo_url: string;
  status: UserStatus;
  security: UserSecurity;
  country: Country;
  preferred_currency: Currency;
  stats: UserStats;
  roles: Role[];
  wallet: Wallet;
  merchant_profiles: MerchantProfile[] | null;
  created_at: string;
  updated_at: string;
}
