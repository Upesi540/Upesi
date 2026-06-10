import type{ Market } from './market';
import type { ServiceCategory } from './service';

export interface appInitData {
  markets: Market[];
  service_categories: ServiceCategory[];
}