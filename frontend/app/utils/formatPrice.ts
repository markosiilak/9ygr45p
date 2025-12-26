interface PriceInput {
  price?: number
  amount?: number
  currency?: string
  currency_code?: string
}

export default function formatPrice(t: PriceInput | null | undefined): string {
  if (t == null) return '-'
  const amount = t.price ?? t.amount
  const currency = t.currency ?? t.currency_code ?? 'EUR'
  if (typeof amount === 'number') return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(amount)
  return String(amount || '-')
}
