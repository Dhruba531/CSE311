
export interface MarketData {
  time: string;
  price: number;
}

export interface NewsItem {
  id: string;
  title: string;
  summary: string;
  timestamp: string;
}

export enum AppView {
  LOGIN = 'LOGIN',
  DASHBOARD = 'DASHBOARD'
}
