/**
 * Event bus sederhana utk komunikasi antar modul
 * (contoh: api.js → AuthContext saat 401).
 */
const listeners = {};

export function on(event, fn) {
  if (!listeners[event]) listeners[event] = [];
  listeners[event].push(fn);
  return () => off(event, fn);
}

export function off(event, fn) {
  listeners[event] = (listeners[event] || []).filter(f => f !== fn);
}

export function emit(event, payload) {
  (listeners[event] || []).forEach(fn => fn(payload));
}