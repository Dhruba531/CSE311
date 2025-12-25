
import React, { useState, useEffect } from 'react';
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import { getMarketInsights } from '../services/geminiService';

const data = [
  { time: '09:00', price: 65400 },
  { time: '10:00', price: 65800 },
  { time: '11:00', price: 65200 },
  { time: '12:00', price: 66100 },
  { time: '13:00', price: 66900 },
  { time: '14:00', price: 67200 },
  { time: '15:00', price: 66800 },
];

const Dashboard: React.FC<{ onLogout: () => void }> = ({ onLogout }) => {
  const [insight, setInsight] = useState<string>('Loading AI insights...');

  useEffect(() => {
    const fetchInsight = async () => {
      const result = await getMarketInsights('BTC/USD');
      setInsight(result || 'No insight available.');
    };
    fetchInsight();
  }, []);

  return (
    <div className="w-full max-w-6xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
      <header className="flex justify-between items-center py-6 border-b border-zinc-100">
        <div>
          <h2 className="text-2xl font-black tracking-tighter uppercase">Trade<span className="text-zinc-300">X</span> Terminal</h2>
          <p className="text-[11px] font-bold text-zinc-400 uppercase tracking-widest mt-1">Market Insight Engine Active</p>
        </div>
        <button
          onClick={onLogout}
          className="px-6 py-2 text-[10px] font-bold uppercase tracking-widest bg-zinc-50 border border-zinc-100 hover:bg-black hover:text-white transition-all duration-300"
        >
          Deauthorize
        </button>
      </header>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-6">
          <div className="p-8 bg-white border border-zinc-100 rounded-none shadow-sm relative overflow-hidden">
            <div className="flex justify-between items-center mb-10">
              <div className="flex items-center space-x-4">
                <div className="w-12 h-12 bg-black flex items-center justify-center text-white text-xl">
                  <i className="fa-brands fa-bitcoin"></i>
                </div>
                <div>
                  <h3 className="font-black text-xl tracking-tight uppercase">Bitcoin Core</h3>
                  <p className="text-[10px] text-zinc-400 font-bold uppercase tracking-[0.2em]">BTC / USD Real-time</p>
                </div>
              </div>
              <div className="text-right">
                <p className="text-2xl font-black tracking-tighter">$66,842.12</p>
                <p className="text-[10px] font-black text-emerald-500 uppercase tracking-widest">+2.45% Yield</p>
              </div>
            </div>
            <div className="h-[320px] w-full">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={data}>
                  <defs>
                    <linearGradient id="colorPrice" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#000000" stopOpacity={0.05}/>
                      <stop offset="95%" stopColor="#000000" stopOpacity={0}/>
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f5f5f5" />
                  <XAxis dataKey="time" stroke="#d4d4d8" fontSize={10} tickLine={false} axisLine={false} dy={10} />
                  <YAxis hide domain={['dataMin - 500', 'dataMax + 500']} />
                  <Tooltip 
                    cursor={{ stroke: '#000', strokeWidth: 1 }}
                    contentStyle={{ borderRadius: '0px', border: '1px solid #000', boxShadow: '10px 10px 0px rgba(0,0,0,0.05)', fontSize: '11px', fontWeight: 'bold' }}
                  />
                  <Area type="stepAfter" dataKey="price" stroke="#000000" strokeWidth={2} fillOpacity={1} fill="url(#colorPrice)" />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </div>

          <div className="grid grid-cols-3 gap-4">
            {['Peak', 'Floor', 'Liquidity'].map((label, idx) => (
              <div key={idx} className="p-6 bg-zinc-50 border border-zinc-100 flex flex-col justify-center">
                <p className="text-[9px] font-bold text-zinc-400 uppercase tracking-[0.3em] mb-2">{label}</p>
                <p className="text-lg font-black tracking-tight">
                  {idx === 0 ? '$67,402' : idx === 1 ? '$65,110' : '42.1B'}
                </p>
              </div>
            ))}
          </div>
        </div>

        <div className="space-y-6 flex flex-col">
          <div className="p-8 bg-black text-white flex flex-col flex-1 relative overflow-hidden">
             {/* Decorative abstract element */}
            <div className="absolute top-0 right-0 w-32 h-32 bg-white/5 -mr-16 -mt-16 rotate-45"></div>
            
            <h3 className="font-bold text-[10px] uppercase tracking-[0.4em] flex items-center space-x-3 mb-6">
              <span className="flex h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
              <span>Gemini Intelligence</span>
            </h3>
            <div className="flex-1 text-sm leading-relaxed text-zinc-300 font-medium">
              "{insight}"
            </div>
            <div className="mt-8 pt-6 border-t border-white/10 text-[9px] text-zinc-500 font-bold uppercase tracking-widest">
              XTrade Core v4.0.1
            </div>
          </div>

          <div className="p-8 bg-white border border-zinc-100 shadow-sm">
            <h3 className="font-black text-xs uppercase tracking-[0.3em] mb-6">Log Journal</h3>
            <div className="space-y-6">
              {[1, 2, 3].map((item) => (
                <div key={item} className="flex justify-between items-center group cursor-pointer">
                  <div className="flex items-center space-x-4">
                    <div className={`w-1 h-8 ${item % 2 === 0 ? 'bg-zinc-100' : 'bg-black'}`}></div>
                    <div>
                      <p className="text-[11px] font-black uppercase tracking-tight">{item % 2 === 0 ? 'Liquidated' : 'Executed'}</p>
                      <p className="text-[9px] text-zinc-400 font-bold uppercase tracking-tighter">04:1{item} UTC</p>
                    </div>
                  </div>
                  <p className={`text-[11px] font-black ${item % 2 === 0 ? 'text-zinc-400' : 'text-black'}`}>
                    {item % 2 === 0 ? '-$2.4K' : '+$12.5K'}
                  </p>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
