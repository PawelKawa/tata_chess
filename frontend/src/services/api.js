const BASE_URL = import.meta.env.VITE_API_URL ?? ''

async function request(path) {
  const response = await fetch(`${BASE_URL}${path}`)
  if (!response.ok) {
    throw new Error(`API error: ${response.status} ${path}`)
  }
  return response.json()
}

export const api = {
  getPosts: () => request('/api/posts'),
  getPost:  (slug) => request(`/api/posts/${slug}`),

  sendContact: (data) => {
    return fetch(`${BASE_URL}/api/contact`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(data),
    }).then(async (res) => {
      const json = await res.json()
      if (!res.ok) throw json
      return json
    })
  },
}
