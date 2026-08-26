export const mockBusinesses = [
  {
    id: 1,
    slug: 'luna-spa-studio',
    name: 'Luna Spa Studio',
    description: 'Luxury facial and massage experiences for deep relaxation.',
    city: 'New York',
    category: 'Massage',
    rating: 4.9,
  },
  {
    id: 2,
    slug: 'vega-wellness-club',
    name: 'Vega Wellness Club',
    description: 'Holistic wellness rituals and rejuvenation therapies.',
    city: 'Los Angeles',
    category: 'Wellness',
    rating: 4.8,
  },
]

export const mockServices = [
  {
    id: 1,
    business_id: 1,
    name: 'Signature Massage',
    description: '60-minute full-body massage with aromatherapy oils.',
    price: 120,
    duration: '60 min',
    slug: 'signature-massage',
  },
  {
    id: 2,
    business_id: 2,
    name: 'Glow Facial',
    description: 'Vitamin-rich facial for hydration and skin radiance.',
    price: 110,
    duration: '45 min',
    slug: 'glow-facial',
  },
]
