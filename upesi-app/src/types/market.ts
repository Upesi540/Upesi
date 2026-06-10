// import type { Crop } from "./crop"

/**
 * Interface pour une Catégorie de produits (ex: Céréales, Tubercule)
 */
export interface Category {
  id: string;
  name: string;
  icon:string;
  slug: string;
  market_id: string;
  description?: string;

}

/**
 * Interface pour un Marché (ex: Matières Premières, Intrants)
 */
export interface Market {
  id: string;
  name: string;
  slug: string;
  description?: string;
  image?: string;   // URL complète via Storage::url()
  banner?: string;  // URL complète via Storage::url()
  is_active: boolean;
  sort_order: number;
  // Relation vers les catégories du marché
  categories?: Category[];
}