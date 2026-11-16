import { Link } from 'react-router-dom';
import { imageUrl } from '../api';

export default function PhotoCard({ photo }) {
  const owner = photo.userId ? `${photo.userId.firstName} ${photo.userId.lastName}` : 'Unknown';

  return (
    <article className="photo-card">
      <Link to={`/photos/${photo._id}`}>
        <img src={imageUrl(photo.fileName)} alt={photo.title} loading="lazy" />
      </Link>
      <div className="photo-card-body">
        <h3>{photo.title}</h3>
        <p className="meta">by {owner} · {new Date(photo.dateTime).toLocaleDateString()}</p>
      </div>
    </article>
  );
}
