import { api } from './api.js'

export const contactService = {
  submit: (payload) => api.post('/api/contact', payload),
}
