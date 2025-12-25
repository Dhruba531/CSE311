
import React, { useState } from 'react';
import LoginForm from './components/LoginForm';
import Dashboard from './components/Dashboard';
import { AppView } from './types';

const App: React.FC = () => {
  const [view, setView] = useState<AppView>(AppView.LOGIN);

  const handleLogin = () => {
    setView(AppView.DASHBOARD);
  };

  const handleLogout = () => {
    setView(AppView.LOGIN);
  };

  return (
    <main className="min-h-screen flex flex-col bg-white">
      {view === AppView.LOGIN ? (
        <div className="flex-1 flex items-center justify-center p-6 bg-zinc-50/50">
          <LoginForm onLogin={handleLogin} />
        </div>
      ) : (
        <div className="p-4 md:p-8">
          <Dashboard onLogout={handleLogout} />
        </div>
      )}

      {/* Footer */}
      <footer className="py-8 px-12 text-center text-[10px] text-zinc-400 font-bold uppercase tracking-[0.2em] bg-white">
        &copy; 2024 TradeX Terminal Global
        <span className="mx-4 text-zinc-200">|</span>
        <a href="#" className="hover:text-black transition-colors">Privacy</a>
        <span className="mx-4 text-zinc-200">|</span>
        <a href="#" className="hover:text-black transition-colors">Terms</a>
      </footer>
    </main>
  );
};

export default App;
