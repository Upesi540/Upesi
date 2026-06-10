// quasar/src/types/api.d.t

// Types utilitaires pour les données JSON
export type JsonPrimitive = string | number | boolean | null
export type JsonValue = JsonPrimitive | JsonObject | JsonArray
export interface JsonObject { [key: string]: JsonValue }
export type JsonArray = JsonValue[]

// Type pour les métadonnées de pagination
export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from?: number
  to?: number
  path?: string
  links?: {
    first?: string
    last?: string
    prev?: string | null
    next?: string | null
  }
}

export interface ApiResponse<T> {
  status: boolean
  message: string
  meta: PaginationMeta | null  // ✅ plus spécifique
  data: T
}