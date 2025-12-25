
import { StockData, ChartPoint } from './types';

export const AAPL_MOCK: StockData = {
  symbol: 'AAPL',
  name: 'Apple Inc.',
  exchange: 'NYSE',
  price: 150.03,
  change: 1.50,
  changePercent: 1.01,
  open: '147.00',
  prevClose: '148.50',
  high: '153.00',
  low: '145.50',
  volume: '3.31M',
  turnover: '--',
  mktCap: '1.48B',
  peRatio: '17.22',
  fiftyTwoWkHigh: '225.00',
  fiftyTwoWkLow: '90.00',
  status: 'Closed',
  timestamp: '05:33 EST'
};

export const MOCK_CHART_DATA: ChartPoint[] = [
  { time: '09:30', value: 149.2 },
  { time: '10:00', value: 148.5 },
  { time: '10:35', value: 150.1 },
  { time: '11:00', value: 149.8 },
  { time: '11:40', value: 148.9 },
  { time: '12:00', value: 149.4 },
  { time: '12:45', value: 148.2 },
  { time: '13:50', value: 148.0 },
  { time: '14:30', value: 149.8 },
  { time: '14:55', value: 150.2 },
  { time: '15:30', value: 149.9 },
  { time: '16:00', value: 150.03 },
];
