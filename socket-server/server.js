const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');

const app = express();

app.use(cors());
app.use(express.json());

// Add logging middleware
app.use((req, res, next) => {
    console.log(`📨 HTTP ${req.method} request to: ${req.url}`);
    next();
});

const server = http.createServer(app);

const io = new Server(server, {
    cors: {
        origin: '*',
        methods: ['GET', 'POST']
    }
});

io.on('connection', (socket) => {
    console.log('✅ User connected:', socket.id);

    socket.on('disconnect', () => {
        console.log('❌ User disconnected:', socket.id);
    });
});

// Add a test endpoint
app.get('/test', (req, res) => {
    console.log('Test endpoint accessed');
    res.json({ message: 'Server is running!' });
});

app.post('/new-property', (req, res) => {
    console.log('📦 NEW PROPERTY REQUEST RECEIVED!');
    console.log('Request body:', req.body);
    
    const property = req.body;
    
    console.log('Broadcasting to all connected clients...');
    io.emit('property-added', property);
    console.log('✅ Broadcast complete');
    
    return res.json({
        success: true,
        message: 'Property broadcasted'
    });
});

app.post('/update-property', (req, res) => {
    console.log('✏️ UPDATE PROPERTY REQUEST RECEIVED!');
    const property = req.body;
    io.emit('property-updated', property);
    return res.json({ success: true, message: 'Property update broadcasted' });
});

app.post('/archive-property', (req, res) => {
    console.log('📦 ARCHIVE PROPERTY REQUEST RECEIVED!');
    const data = req.body;
    io.emit('property-archived', data);
    return res.json({ success: true, message: 'Property archived broadcasted' });
});

app.post('/unarchive-property', (req, res) => {
    console.log('📦 UNARCHIVE PROPERTY REQUEST RECEIVED!');
    const data = req.body;
    io.emit('property-unarchived', data);
    return res.json({ success: true, message: 'Property unarchived broadcasted' });
});

app.post('/delete-property', (req, res) => {
    console.log('🗑️ DELETE PROPERTY REQUEST RECEIVED!');
    const data = req.body;
    io.emit('property-deleted', data);
    return res.json({ success: true, message: 'Property deleted broadcasted' });
});

const PORT = 3000;

server.listen(PORT, () => {
    console.log(`🚀 WebSocket server running on port ${PORT}`);
    console.log(`📍 Waiting for connections from PHP at http://localhost:${PORT}`);
    console.log(`🧪 Test endpoint: http://localhost:${PORT}/test`);
});