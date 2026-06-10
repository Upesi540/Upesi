export interface MarketTrend {
  crop_id: string;
  name: string;
  price: number;
  volume: number;
  unit: string;
  volume_full: string;
  change: number;
  status: 'up' | 'down' | 'stable';
  color: 'positive' | 'negative' | 'grey';
  icon: string;
}

export interface TickerResponse {
  country_id: string;
  is_detected: boolean;
  trends: MarketTrend[];
  data_count: number;
}
