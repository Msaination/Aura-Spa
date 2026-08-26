import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { treatmentMenu } from '@/data/treatments'

const featuredTreatments = [
  treatmentMenu.find((item) => item.name === 'Aromatherapy Full Body (Detox, calming)'),
  treatmentMenu.find((item) => item.name === 'Biomedical Emporium Facial'),
  treatmentMenu.find((item) => item.name === 'Gel Overlay'),
].filter(Boolean) as typeof treatmentMenu

export function ServiceHighlights() {
  return (
    <section id="treatments" className="mx-auto max-w-6xl px-6 py-12 md:py-20">
      <div className="mb-8 flex items-end justify-between gap-4">
        <div>
          <p className="text-xs font-medium uppercase tracking-[0.22em] text-[#8c7267]">
            Treatment menu
          </p>
          <h2 className="mt-2 text-3xl font-semibold tracking-[-0.04em] text-[#2b2624] md:text-4xl">
            Signature spa treatments.
          </h2>
        </div>
        <Button variant="secondary" className="rounded-full border-[#dbc6b8] bg-[#f6efe9] text-[#2b2624]">
          View all
        </Button>
      </div>

      <div className="grid gap-6 md:grid-cols-3">
        {featuredTreatments.map((plan, index) => (
          <Card key={`${plan.name}-${index}`} className="overflow-hidden border-[#eadfd5] bg-white/80">
            <div
              className="h-40"
              style={{
                backgroundColor: ['#dcbfb4', '#caa595', '#e7d7c4'][index % 3],
              }}
            />
            <CardHeader>
              <div className="flex items-start justify-between gap-3">
                <CardTitle className="text-[#2a241f]">{plan.name}</CardTitle>
                {plan.price ? (
                  <span className="text-sm font-semibold text-[#5d4f49]">{plan.price}</span>
                ) : null}
              </div>
            </CardHeader>
            <CardContent>
              <p className="text-xs font-medium uppercase tracking-[0.18em] text-[#8c7267]">
                {plan.category}
              </p>
              {plan.duration ? (
                <p className="mt-2 text-sm text-[#604e48]">{plan.duration}</p>
              ) : null}
            </CardContent>
          </Card>
        ))}
      </div>
    </section>
  )
}
