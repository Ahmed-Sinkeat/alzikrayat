import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { errorMessage } from '../api';

export default function LoginForm({ onSuccess }) {
  const { login } = useAuth();
  const [form, setForm] = useState({ email: '', password: '' });
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  const change = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  async function submit(e) {
    e.preventDefault();
    setError('');
    setBusy(true);
    try {
      await login(form.email, form.password);
      onSuccess();
    } catch (err) {
      setError(errorMessage(err));
      setBusy(false);
    }
  }

  return (
    <form onSubmit={submit} className="form">
      <h2>Log in</h2>
      {error && <p className="error">{error}</p>}
      <label>Email
        <input type="email" name="email" value={form.email} onChange={change} required autoComplete="email" />
      </label>
      <label>Password
        <input type="password" name="password" value={form.password} onChange={change} required autoComplete="current-password" />
      </label>
      <button type="submit" disabled={busy}>{busy ? 'Logging in…' : 'Log in'}</button>
    </form>
  );
}
