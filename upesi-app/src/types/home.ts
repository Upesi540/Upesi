// quasar/src/types/api.d.ts

import type{ Crop } from "./crop"
import type{ Category, Market } from "./market"
import type{ Partner } from "./partner"
import type{ Product } from "./product"
import type{ ServiceOffer } from "./serviceOffer"
import type{ Slide } from "./slide"



export interface HomeStatsExtended {
  total_products: number
  active_markets: number
  product_categories: number
  crop_varieties: number
  active_farmers: number
  active_buyers: number
  avg_products_per_farmer: number
}


// HomeData mis à jour
export interface HomeData {
  slides: Slide[]
  markets: Market[]
  categories: Category[]
  popular_crops: Crop[]
  featured_products: Product[]
  featured_services:ServiceOffer[]
  stats: HomeStatsExtended
  partners: Partner[]
}
