import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function Home() {
  const { user } = useAuth();

  return (
    <section className="hero">
      <h1>Alzikrayat</h1>
      <p>Share the moments worth remembering.</p>
      <div className="hero-actions">
        <Link to="/gallery" className="button">Browse the gallery</Link>
        {!user && <Link to="/login" className="button secondary">Join us</Link>}
      </div>
    </section>
  );
}
