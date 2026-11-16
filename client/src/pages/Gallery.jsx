import { useEffect, useState } from 'react';
import api, { errorMessage } from '../api';
import PhotoCard from '../components/PhotoCard';

export default function Gallery() {
  const [photos, setPhotos] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const controller = new AbortController();
    api.get('/photos', { signal: controller.signal })
      .then(({ data }) => setPhotos(data.photos))
      .catch((err) => { if (!controller.signal.aborted) setError(errorMessage(err)); })
      .finally(() => { if (!controller.signal.aborted) setLoading(false); });
    return () => controller.abort();
  }, []);

  if (loading) return <p>Loading photos…</p>;
  if (error) return <p className="error">{error}</p>;

  return (
    <section>
      <h1>Gallery</h1>
      {photos.length === 0 ? (
        <p>No photos yet. Be the first to upload one!</p>
      ) : (
        <div className="gallery">
          {photos.map((p) => <PhotoCard key={p._id} photo={p} />)}
        </div>
      )}
    </section>
  );
}
