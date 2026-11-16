import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import api, { errorMessage, imageUrl } from '../api';
import { useAuth } from '../context/AuthContext';
import CommentForm from '../components/CommentForm';

export default function PhotoDetail() {
  const { id } = useParams();
  const { user } = useAuth();
  const [photo, setPhoto] = useState(null);
  const [comments, setComments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const controller = new AbortController();
    setLoading(true);
    setError('');
    api.get(`/photos/${id}`, { signal: controller.signal })
      .then(({ data }) => { setPhoto(data.photo); setComments(data.comments); })
      .catch((err) => { if (!controller.signal.aborted) setError(errorMessage(err)); })
      .finally(() => { if (!controller.signal.aborted) setLoading(false); });
    return () => controller.abort();
  }, [id]);

  if (loading) return <p>Loading…</p>;
  if (error) return <p className="error">{error} <Link to="/gallery">Back to the gallery</Link></p>;

  const owner = photo.userId ? `${photo.userId.firstName} ${photo.userId.lastName}` : 'Unknown';

  return (
    <article className="photo-detail">
      <Link to="/gallery">← Gallery</Link>
      <img src={imageUrl(photo.fileName)} alt={photo.title} className="photo-full" />
      <h1>{photo.title}</h1>
      <p className="meta">by {owner} · {new Date(photo.dateTime).toLocaleString()}</p>
      {photo.description && <p>{photo.description}</p>}

      <section className="comments">
        <h2>Comments ({comments.length})</h2>
        {comments.length === 0 && <p>No comments yet.</p>}
        <ul>
          {comments.map((c) => (
            <li key={c._id}>
              <strong>{c.userId ? `${c.userId.firstName} ${c.userId.lastName}` : 'Unknown'}</strong>
              <span className="meta"> · {new Date(c.dateTime).toLocaleString()}</span>
              <p>{c.comment}</p>
            </li>
          ))}
        </ul>
        {user ? (
          <CommentForm photoId={photo._id} onAdded={(c) => setComments([...comments, c])} />
        ) : (
          <p><Link to="/login" state={{ from: `/photos/${photo._id}` }}>Log in</Link> to comment.</p>
        )}
      </section>
    </article>
  );
}
