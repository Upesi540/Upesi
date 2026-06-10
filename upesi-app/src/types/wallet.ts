import type{ FormattedCurrency } from "./user"

export interface Currency {
  id: string
  code: string
  name: string
  symbol: string
  exchange_rate: string
  is_base: boolean
  is_active: boolean
  precision: number
  is_crypto: number
  formatted: FormattedCurrency
  exchange_rate_date: string
}

export interface Wallet {
  id: string
  available_balance: number
  frozen_balance: string | null
  total_balance: number
  formatted_available: string
  is_active: boolean
  is_primary: boolean
  currency: Currency
  created_at: string
}
