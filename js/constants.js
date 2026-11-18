const ORDER_STATUS = Object.freeze({
    PENDING: 'pending',
    PAID: 'paid',
    SHIPPED: 'shipped',
    DELIVERED: 'delivered',
    CANCELLED: 'cancelled',
});

const ORDER_STATUS_LABELS = Object.freeze({
    [ORDER_STATUS.PENDING]: 'Pending',
    [ORDER_STATUS.PAID]: 'Paid',
    [ORDER_STATUS.SHIPPED]: 'Shipped',
    [ORDER_STATUS.DELIVERED]: 'Delivered',
    [ORDER_STATUS.CANCELLED]: 'Cancelled',
});

const CART_STORAGE_KEY = 'goldScentsCart';
const LAST_ORDER_STORAGE_KEY = 'goldScentsLastOrder';
const CURRENCY_PREFIX = 'GH₵';
const WHATSAPP_NUMBER = '233534048714';
const MOMO_NUMBER = '0598488853';
const MOMO_ACCOUNT_NAME = 'Gold Scents';
const MIN_STOCK_AVAILABLE = 1;
const LOW_STOCK_THRESHOLD = 3;
const EMPTY_CART_LENGTH = 0;

const ORDER_STATUS_STEPS = Object.freeze([
    ORDER_STATUS.PENDING,
    ORDER_STATUS.PAID,
    ORDER_STATUS.SHIPPED,
    ORDER_STATUS.DELIVERED,
]);
