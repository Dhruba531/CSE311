
import React, { useState } from 'react';

interface LoginFormProps {
  onLogin: () => void;
}

const LoginForm: React.FC<LoginFormProps> = ({ onLogin }) => {
  const [email, setEmail] = useState('terminal@tradex.com');
  const [password, setPassword] = useState('password');
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    // Simulate API call
    setTimeout(() => {
      setIsLoading(false);
      onLogin();
    }, 1200);
  };

  return (
    <div className="w-full max-w-[440px] p-10 md:p-12 bg-white border border-zinc-100 rounded-[2.5rem] shadow-2xl shadow-black/[0.03] animate-in fade-in zoom-in-95 duration-700">
      <div className="mb-10 text-center">
        <h1 className="text-5xl font-black tracking-tighter mb-3 uppercase">
          Trade<span className="text-zinc-300">X</span>
        </h1>
        <p className="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.5em]">
          Institutional Access
        </p>
      </div>

      <div className="flex flex-col gap-3 mb-10">
        <button className="w-full py-4 px-4 border border-zinc-100 rounded-2xl flex items-center justify-center space-x-3 hover:bg-zinc-50 transition-all font-bold text-[10px] uppercase tracking-[0.2em]">
          <i className="fa-brands fa-google text-base"></i>
          <span>Sign in with Google</span>
        </button>
        <button className="w-full py-4 px-4 border border-zinc-100 rounded-2xl flex items-center justify-center space-x-3 hover:bg-zinc-50 transition-all font-bold text-[10px] uppercase tracking-[0.2em]">
          <i className="fa-brands fa-apple text-lg"></i>
          <span>Sign in with Apple</span>
        </button>
      </div>

      <div className="relative mb-10">
        <div className="absolute inset-0 flex items-center">
          <span className="w-full border-t border-zinc-100"></span>
        </div>
        <div className="relative flex justify-center text-[10px] uppercase">
          <span className="bg-white px-4 text-zinc-300 font-bold tracking-[0.3em]">Direct Terminal Access</span>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        <div className="group">
          <label className="block text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400 mb-2 ml-1 transition-colors group-focus-within:text-black">
            Identity / Email
          </label>
          <input
            type="text"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className="w-full px-5 py-4 bg-zinc-50 border border-zinc-100 rounded-2xl focus:border-black focus:bg-white transition-all duration-300 text-sm font-medium tracking-tight outline-none"
            placeholder="terminal@tradex.com"
            required
          />
        </div>

        <div className="group">
          <div className="flex justify-between items-center mb-2 ml-1">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400 transition-colors group-focus-within:text-black">
              Passcode
            </label>
            <a href="#" className="text-[10px] font-bold text-zinc-300 hover:text-black hover:underline uppercase tracking-tighter transition-colors">
              Reset
            </a>
          </div>
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            className="w-full px-5 py-4 bg-zinc-50 border border-zinc-100 rounded-2xl focus:border-black focus:bg-white transition-all duration-300 text-sm font-medium outline-none"
            placeholder="••••••••"
            required
          />
        </div>

        <button
          type="submit"
          disabled={isLoading}
          className="w-full py-5 bg-black text-white rounded-2xl font-bold text-[11px] uppercase tracking-[0.4em] hover:bg-zinc-800 transition-all duration-300 transform active:scale-[0.98] flex items-center justify-center space-x-2 mt-4 shadow-xl shadow-black/5"
        >
          {isLoading ? (
            <div className="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin" />
          ) : (
            <span>Authorize Terminal</span>
          )}
        </button>
      </form>

      <div className="mt-12 text-center">
        <button className="text-[10px] text-zinc-400 font-bold uppercase tracking-[0.3em] hover:text-black transition-colors">
          Open New Account
        </button>
      </div>
    </div>
  );
};

export default LoginForm;
