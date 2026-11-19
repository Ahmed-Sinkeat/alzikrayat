import { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import api, { errorMessage, imageUrl } from '../api';
import { useAuth } from '../context/AuthContext';
import CommentForm from '../components/CommentForm';
import socket from '../socket';

// append comments that aren't in the list yet (the author gets theirs from the API and the socket)
const mergeComments = (list, incoming) => [
  ...list,
  ...incoming.filter((c) => !list.some((x) => x._id === c._id)),
];

export default function PhotoDetail() {
  const { id } = useParams();
  const { user } = useAuth();
  const navigate = useNavigate();
  const [photo, setPhoto] = useState(null);
  const [comments, setComments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const controller = new AbortController();
    setLoading(true);
    setError('');
    setComments([]);
    api.get(`/photos/${id}`, { signal: controller.signal })
      // keep any comment that arrived live while this request was in flight
      .then(({ data }) => { setPhoto(data.photo); setComments((prev) => mergeComments(data.comments, prev)); })
      .catch((err) => { if (!controller.signal.aborted) setError(errorMessage(err)); })
      .finally(() => { if (!controller.signal.aborted) setLoading(false); });
    return () => controller.abort();
  }, [id]);

  // live comments: join this photo's room, listen for new ones, leave + unsubscribe on unmount
  useEffect(() => {
    const join = () => socket.emit('photo:join', id);
    const onComment = (c) => setComments((prev) => mergeComments(prev, [c]));
    join();
    socket.on('connect', join); // rejoin after a reconnect
    socket.on('comment:new', onComment);
    return () => {
      socket.off('connect', join);
      socket.off('comment:new', onComment);
      socket.emit('photo:leave', id);
    };
  }, [id]);

  async function remove() {
    if (!window.confirm('Delete this photo and its comments?')) return;
    try {
      await api.delete(`/photos/${photo._id}`);
      navigate('/gallery');
    } catch (err) {
      setError(errorMessage(err));
    }
  }

  if (loading) return <p>Loading…</p>;
  if (error) return <p className="error">{error} <Link to="/gallery">Back to the gallery</Link></p>;

  const isOwner = user && photo.userId && photo.userId._id === user._id;
  const owner = photo.userId ? `${photo.userId.firstName} ${photo.userId.lastName}` : 'Unknown';

  return (
    <article className="photo-detail">
      <Link to="/gallery">← Gallery</Link>
      <img src={imageUrl(photo.fileName)} alt={photo.title} className="photo-full" />
      <h1>{photo.title}</h1>
      <p className="meta">by {owner} · {new Date(photo.dateTime).toLocaleString()}</p>
      {photo.description && <p>{photo.description}</p>}
      {isOwner && <button type="button" className="danger" onClick={remove}>Delete photo</button>}

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
          <CommentForm photoId={photo._id} onAdded={(c) => setComments((prev) => mergeComments(prev, [c]))} />
        ) : (
          <p><Link to="/login" state={{ from: `/photos/${photo._id}` }}>Log in</Link> to comment.</p>
        )}
      </section>
    </article>
  );
}
