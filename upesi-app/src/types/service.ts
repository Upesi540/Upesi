// src/types/service.ts

export interface Service {
  id: string;
  name: string;
  slug: string;
  description?: string;
  icon?: string;
  image?: string; // Correspond au Storage::url(image_path)
  is_active: boolean;
  sort_order: number;
  service_category_id: string;
}

/**
 * Interface pour une Catégorie de Service (ex: Logistique, Préparation du sol)
 * Correspond au parent
 */
export interface ServiceCategory {
  id: string;
  name: string;
  slug: string;
  description?: string;
  icon?: string;
  banner?: string;
  is_active: boolean;
  sort_order: number;
  // Relation HasMany : Une catégorie contient plusieurs services
  services?: Service[]; 
}

