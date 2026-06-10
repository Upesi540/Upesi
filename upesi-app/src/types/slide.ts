export interface Slide {
  id: string
  title: string
  sub_title: string
  button_text: string
  link_type: string
  link_url: string
  button_color: string
  button_text_color: string
  image_url: string | null
  order: number
  is_active: boolean
}