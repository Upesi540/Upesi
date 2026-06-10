
export interface Country {
  id: string
  name: string
  iso3: string
  iso2: string
  phone_code: string
  capital: string
  currency: string
  native: string
  emoji: string
  emojiU: string | null
}

export interface State {
  id: string;
  name: string;
  iso2: string;
  country_id: string;
}

export interface City {
  id: string;
  name: string;
  state_id: string;
}
export interface ILocation {
  city: string
  state: string
  country: string
  address: string
  warehouse: string|null
  coordinates: {
    lat: string
    lng: string
  }
}
