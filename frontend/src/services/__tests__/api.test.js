import { describe, it, expect, vi, beforeEach } from 'vitest'
import { api } from '../api.js'

describe('api service', () => {
  beforeEach(() => {
    vi.stubGlobal('fetch', vi.fn())
  })

  it('getPosts calls correct URL', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => [{ id: 1, title: 'Test' }],
    })

    const posts = await api.getPosts()

    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/posts')
    )
    expect(posts).toHaveLength(1)
  })

  it('getPost calls correct URL with slug', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ id: 1, slug: 'test-post' }),
    })

    const post = await api.getPost('test-post')

    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/posts/test-post')
    )
    expect(post.slug).toBe('test-post')
  })

  it('getPosts throws on non-ok response', async () => {
    fetch.mockResolvedValueOnce({ ok: false, status: 500 })

    await expect(api.getPosts()).rejects.toThrow()
  })
})
