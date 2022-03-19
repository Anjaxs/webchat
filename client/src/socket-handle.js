import env from '@utils/env';
import socket from './socket';
import store from './store';

// 登录，并进入房间
export async function handleInit({
  name,
  token,
  src,
  roomList
}) {
    // 此处逻辑需要抽离复用
  socket.emit('login', {name, token, ...env});
  roomList.forEach(item => {
    const obj = {
      token,
      name,
      src,
      roomid: item,
    };
    socket.emit('room', obj);
  })
  // await store.dispatch('getRoomHistory', { selfId: id })
}

// 读取房间未读消息
export async function readMessages({
  token,
  roomid
}) {
  socket.emit('read_messages', {token, roomid});
}

// 发送消息
export async function sendMessage(obj) {
  obj.token = store.state.userInfo.token;
  socket.emit('message', obj)
}