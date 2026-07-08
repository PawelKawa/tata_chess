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
}
