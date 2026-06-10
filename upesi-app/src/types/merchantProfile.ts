export interface MerchantProfile {
    id: string;
    shop_name: string;
    type: 'producer' | 'supplier' | 'trader' | 'provider' | 'transporter';
    type_label: string; // ex: "Producteur", "Transporteur"
    status: 'pending' | 'approved' | 'rejected';
    phone: string | null;
    description: string | null;
    logo_path: string | null;
    logo_url: string | null; // URL complète si nécessaire
    metadata: Record<string, null> | null; // champs spécifiques (crops, vehicle_types, etc.)
    created_at: string;
    updated_at: string;
}
