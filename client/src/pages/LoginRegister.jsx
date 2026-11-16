import { Navigate, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import LoginForm from '../components/LoginForm';
import RegisterForm from '../components/RegisterForm';

export default function LoginRegister() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const from = location.state?.from || '/gallery';

  // already logged in: nothing to do here
  if (user) return <Navigate to={from} replace />;

  const done = () => navigate(from, { replace: true });

  return (
    <div className="auth-grid">
      <LoginForm onSuccess={done} />
      <RegisterForm onSuccess={done} />
    </div>
  );
}
