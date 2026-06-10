import type { JsonObject } from './api'

export interface NewsCategory {
  id: string
  name: string
  slug: string
  icon?: string
  color?: string
  is_active: boolean
  sort_order: number
  articles_count?: number
}

export interface MarketNews {
  id: string
  title: string
  slug: string
  excerpt: string
  content: JsonObject | null
  featured_image: string | null
  type: 'flash' | 'normal' | 'analysis'
  priority: 'low' | 'normal' | 'high' | 'urgent'
  is_pinned: boolean
  is_active: boolean
  reading_time: string
  formatted_date: string
  published_at: string
  tags: string[]
  author?: {
    id: string
    name: string
    avatar: string | null
  }
  category?: {
    id: string
    name: string
    slug: string
  }
  meta?: {
    title: string
    description: string
    keywords: string
  }
  is_expired?: boolean
  is_urgent?: boolean
}
