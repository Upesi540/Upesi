export interface Unit {
  id: string
  name: string
  symbol: string
  description: string | null
  is_active: boolean
  examples?: {
    display: string
    price_format: string
  }
}
