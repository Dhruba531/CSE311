
import React from 'react';
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import { MOCK_CHART_DATA } from '../constants';

const MarketChart: React.FC = () => {
  return (
    <div className="bg-white p-6 rounded-lg border border-gray-100 shadow-sm w-full h-[400px]">
      <div className="flex space-x-4 mb-6">
        {['1D', '5D', '1M', '3M', '1Y', '5Y'].map((range) => (
          <button
            key={range}
            className={`px-3 py-1 text-xs font-bold rounded ${
              range === '1D' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-100'
            }`}
          >
            {range}
          </button>
        ))}
      </div>
      
      <ResponsiveContainer width="100%" height="85%">
        <AreaChart data={MOCK_CHART_DATA}>
          <defs>
            <linearGradient id="colorValue" x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor="#1D4ED8" stopOpacity={0.1}/>
              <stop offset="95%" stopColor="#1D4ED8" stopOpacity={0}/>
            </linearGradient>
          </defs>
          <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#F3F4F6" />
          <XAxis 
            dataKey="time" 
            axisLine={false} 
            tickLine={false} 
            tick={{ fontSize: 10, fill: '#9CA3AF' }}
            minTickGap={30}
          />
          <YAxis 
            orientation="right"
            domain={['dataMin - 0.5', 'dataMax + 0.5']}
            axisLine={false}
            tickLine={false}
            tick={{ fontSize: 10, fill: '#9CA3AF' }}
          />
          <Tooltip 
            contentStyle={{ backgroundColor: '#FFF', border: 'none', borderRadius: '8px', boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)' }}
          />
          <Area 
            type="monotone" 
            dataKey="value" 
            stroke="#1D4ED8" 
            strokeWidth={2}
            fillOpacity={1} 
            fill="url(#colorValue)" 
          />
        </AreaChart>
      </ResponsiveContainer>
    </div>
  );
};

export default MarketChart;
