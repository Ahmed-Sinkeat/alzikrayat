import { useState } from 'react';
import api, { errorMessage } from '../api';

export default function CommentForm({ photoId, onAdded }) {
  const [text, setText] = useState('');
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  async function submit(e) {
    e.preventDefault();
    setError('');
    setBusy(true);
    try {
      const { data } = await api.post(`/photos/${photoId}/comments`, { comment: text });
      onAdded(data.comment);
      setText('');
    } catch (err) {
      setError(errorMessage(err));
    } finally {
      setBusy(false);
    }
  }

  return (
    <form onSubmit={submit} className="form comment-form">
      {error && <p className="error">{error}</p>}
      <label>Add a comment
        <textarea value={text} onChange={(e) => setText(e.target.value)} required maxLength={500} rows={3} />
      </label>
      <button type="submit" disabled={busy || !text.trim()}>{busy ? 'Posting…' : 'Post comment'}</button>
    </form>
  );
}
