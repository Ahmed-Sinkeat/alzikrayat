import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { errorMessage } from '../api';

export default function RegisterForm({ onSuccess }) {
  const { register } = useAuth();
  const [form, setForm] = useState({ firstName: '', lastName: '', email: '', password: '' });
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  const change = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  async function submit(e) {
    e.preventDefault();
    setError('');
    setBusy(true);
    try {
      await register(form);
      onSuccess();
    } catch (err) {
      setError(errorMessage(err));
      setBusy(false);
    }
  }

  return (
    <form onSubmit={submit} className="form">
      <h2>Create an account</h2>
      {error && <p className="error">{error}</p>}
      <label>First name
        <input name="firstName" value={form.firstName} onChange={change} required maxLength={50} />
      </label>
      <label>Last name
        <input name="lastName" value={form.lastName} onChange={change} required maxLength={50} />
      </label>
      <label>Email
        <input type="email" name="email" value={form.email} onChange={change} required autoComplete="email" />
      </label>
      <label>Password (at least 6 characters)
        <input type="password" name="password" value={form.password} onChange={change} required minLength={6} maxLength={72} autoComplete="new-password" />
      </label>
      <button type="submit" disabled={busy}>{busy ? 'Creating…' : 'Register'}</button>
    </form>
  );
}
