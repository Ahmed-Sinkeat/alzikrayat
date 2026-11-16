import { Link, NavLink } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function Navbar() {
  const { user, logout } = useAuth();

  return (
    <header className="navbar">
      <Link to="/" className="brand">Alzikrayat</Link>
      <nav>
        <NavLink to="/gallery">Gallery</NavLink>
        {user ? (
          <>
            <NavLink to="/upload">Upload</NavLink>
            <span className="nav-user">{user.firstName}</span>
            <button type="button" className="link-button" onClick={logout}>Log out</button>
          </>
        ) : (
          <NavLink to="/login">Log in</NavLink>
        )}
      </nav>
    </header>
  );
}
