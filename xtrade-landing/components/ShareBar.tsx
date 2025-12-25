
import React from 'react';

const ShareBar: React.FC = () => {
  const icons = [
    { name: 'Facebook', path: 'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z', color: '#1877F2' },
    { name: 'X', path: 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.45-6.231zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z', color: '#000000' },
    { name: 'LinkedIn', path: 'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z M2 9h4v12H2z M4 2a2 2 0 110 4 2 2 0 010-4z', color: '#0077B5' },
    { name: 'Mail', path: 'M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z M22 6l-10 7L2 6', color: '#718096', isStroke: true }
  ];

  return (
    <div className="fixed bottom-10 left-0 z-40 flex items-center bg-white border border-slate-200 shadow-xl rounded-r p-1 space-x-0 pr-4 animate-in slide-in-from-left duration-500">
      <div className="flex items-center space-x-2 border-r border-slate-200 px-3 py-1 mr-3 group cursor-pointer">
        <svg className="w-3 h-3 text-slate-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" strokeWidth="3" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        <span className="text-[10px] font-bold text-slate-400 uppercase tracking-widest select-none">Share</span>
      </div>
      <div className="flex items-center space-x-3">
        {icons.map((icon) => (
          <button key={icon.name} className="p-1 hover:opacity-70 transition-opacity">
            <svg 
              className="w-4 h-4" 
              viewBox="0 0 24 24" 
              fill={icon.isStroke ? "none" : icon.color}
              stroke={icon.isStroke ? icon.color : "none"}
              strokeWidth={icon.isStroke ? "2" : "0"}
            >
              <path d={icon.path} />
            </svg>
          </button>
        ))}
      </div>
    </div>
  );
};

export default ShareBar;
