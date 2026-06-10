// types/service-offer.ts

export interface ServiceOffer {
    id: string;
    title: string;
    description: string | null;
    price: number;
    price_unit: 'service' | 'heure' | 'jour' | 'km' | 'hectare';
    service_name: string | null;
    service_category: string | null; // slug de la catégorie ('logistique' ou 'prestation')
    merchant: {
        id: string;
        shop_name: string;
        type: 'transporter' | 'provider';
        type_label: string;
        user: {
            id: string;
            name: string; // prénom + nom
        };
    };
    images: string[] | null;
    service_zones: string[] | null;
    created_at: string;
}