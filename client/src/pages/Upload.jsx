import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api, { errorMessage } from '../api';

const MAX_BYTES = 5 * 1024 * 1024;
const TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

export default function Upload() {
  const navigate = useNavigate();
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [file, setFile] = useState(null);
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  function pickFile(e) {
    const f = e.target.files[0];
    setError('');
    if (f && !TYPES.includes(f.type)) {
      setError('Please choose a JPEG, PNG, GIF or WebP image');
      e.target.value = '';
      return setFile(null);
    }
    if (f && f.size > MAX_BYTES) {
      setError('The image must be 5 MB or smaller');
      e.target.value = '';
      return setFile(null);
    }
    setFile(f || null);
  }

  async function submit(e) {
    e.preventDefault();
    if (!file) return setError('Please choose an image');
    setError('');
    setBusy(true);
    try {
      const formData = new FormData();
      formData.append('title', title);
      formData.append('description', description);
      formData.append('image', file);
      const { data } = await api.post('/photos', formData);
      navigate(`/photos/${data.photo._id}`);
    } catch (err) {
      setError(errorMessage(err));
      setBusy(false);
    }
  }

  return (
    <form onSubmit={submit} className="form upload-form">
      <h1>Upload a photo</h1>
      {error && <p className="error">{error}</p>}
      <label>Image
        <input type="file" accept={TYPES.join(',')} onChange={pickFile} required />
      </label>
      <label>Title
        <input value={title} onChange={(e) => setTitle(e.target.value)} required maxLength={100} />
      </label>
      <label>Description (optional)
        <textarea value={description} onChange={(e) => setDescription(e.target.value)} maxLength={500} rows={3} />
      </label>
      <button type="submit" disabled={busy}>{busy ? 'Uploading…' : 'Upload'}</button>
    </form>
  );
}
