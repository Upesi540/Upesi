import type{ ILocation } from './country';
import type{ Crop } from './crop';
import type{ MerchantProfile } from './merchantProfile';
import type { Unit } from './unit';
import type{ User } from './user';


export interface HarvestInfo {
  harvested_at: string;
  quality_grade: string;
  moisture_content: string;
}

export interface Product {
  id: string;
  title: string;
  sku: string;
  description: string;
  images: string[];
  quantity: string;
  min_order_quantity: string;
  unit_price: string;
  status: string;
  harvest_info: HarvestInfo;
  is_featured: boolean;
  created_at: string;
  user: User;
  merchant_profile:MerchantProfile;
  crop: Crop;
  unit: Unit;
  location: ILocation;
}


