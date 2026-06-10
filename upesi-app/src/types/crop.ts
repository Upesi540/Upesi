import type { JsonValue } from './api';
import type { Category } from './market';
import type { Product } from './product';
import type { Unit } from './unit';
export interface Crop {
  id: string;
  name: string;
  variety: string[] | JsonValue | null; // ✅ soit tableau de strings, soit autre structure
  grade: string[] | JsonValue | null; // ✅ soit tableau de strings, soit autre
  scientific_name: string | null;
  reference_price: number | null;
  price_updated_at: string | null;
  description: string | null;
  image_url: string | null;
  category_id: string;
  default_unit: Unit | null;
  quality_standards: JsonValue | null; // ✅ structuré ou générique
  growing_seasons: JsonValue | null; // ✅ structuré ou générique
  growing_days: number | null;
  attributes: JsonValue | null; // ✅ structuré ou générique
  is_active: boolean;
  products_count: number;
  products?: Product[];
  category?: Category;
}
