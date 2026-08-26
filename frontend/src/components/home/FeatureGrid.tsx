import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

const features = [
  {
    title: 'Sensory Wellness Therapy',
    description: 'Choose your preferred aroma from our signature collection of therapeutic oils and settle into a deeply calming ritual.',
  },
  {
    title: 'Thoughtful luxury',
    description: 'Every detail is designed to slow the pace, restore balance and leave you feeling refreshed, uplifted and renewed.',
  },
  {
    title: 'Wellness for every moment',
    description: 'From solo self-care to corporate wellbeing, bridal celebrations and seasonal indulgence, Aura Spa is tailored to your rhythm.',
  },
]

export function FeatureGrid() {
  return (
    <section className="mx-auto max-w-6xl px-6 pb-12 md:pb-20">
      <div className="grid gap-6 md:grid-cols-3">
        {features.map((feature) => (
          <Card key={feature.title} className="bg-[#f8f3ef]">
            <CardHeader>
              <CardTitle className="text-[#2a241f]">{feature.title}</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-base leading-7 text-[#5b4d48]">{feature.description}</p>
            </CardContent>
          </Card>
        ))}
      </div>
    </section>
  )
}
