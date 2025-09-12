import express from 'express';
const router = express.Router();
import { 
    addOrderItems, 
    getMyOrders, 
    getOrderById, 
    updateOrderToPaid, 
    updateOrderToDelivered, 
    getAllOrders 
} from '../controllers/orderController.js';
import { protect } from '../middleware/auth.js';
import { admin } from '../middleware/admin.js';

router.route('/').post(protect, addOrderItems).get(protect, admin, getAllOrders);
router.route('/myorders').get(protect, getMyOrders);
router.route('/:id').get(protect, getOrderById);
router.route('/:id/pay').put(protect, updateOrderToPaid);
router.route('/:id/deliver').put(protect, admin, updateOrderToDelivered);

export default router;
