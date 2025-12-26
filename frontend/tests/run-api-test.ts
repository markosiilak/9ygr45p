/* Simple API feature test: fetch /api/events/1 and ensure JSON has id === 1
   Run with: tsx tests/run-api-test.ts or npm run test:api
*/
import assert from 'assert'

const BASE = process.env.FRONTEND_BASE || 'http://localhost:3000'

interface EventResponse {
  id: number
  [key: string]: unknown
}

async function main(): Promise<void> {
  const res = await fetch(`${BASE}/api/events/1`)
  if (!res.ok) {
    console.error('Request failed', res.status, await res.text())
    process.exit(2)
  }
  const body = await res.json() as EventResponse
  assert.ok(body, 'Response empty')
  assert.strictEqual(body.id, 1, `Expected id=1, got ${body.id}`)
}

main().catch((err: Error) => {
  console.error(err)
  process.exit(1)
})
