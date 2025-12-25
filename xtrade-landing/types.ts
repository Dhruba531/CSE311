
export interface StockData {
  symbol: string;
  name: string;
  exchange: string;
  price: number;
  change: number;
  changePercent: number;
  open: string;
  prevClose: string;
  high: string;
  low: string;
  volume: string;
  turnover: string;
  mktCap: string;
  peRatio: string;
  fiftyTwoWkHigh: string;
  fiftyTwoWkLow: string;
  status: 'Open' | 'Closed';
  timestamp: string;
}

export interface ChartPoint {
  time: string;
  value: number;
}
