export interface Project {
  id: string
  title: string
  slug: string
  description: null
  image_path: string | null
  gallery: string[]
  client: string | null
  start_date: string | null
  end_date: string | null
  status: 'ongoing' | 'completed' | 'planned'
  location: string | null
  testimonials: Testimonial[] | null
  sort_order: number
  is_active: boolean
  duration: string | null
  is_ongoing: boolean
  created_at: string
  updated_at: string
}

// Ajoutez cette interface
export interface Testimonial {
  content?: string
  text?: string
  author?: string
  role?: string
}
