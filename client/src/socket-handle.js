import env from '@utils/env';
import socket from './socket';

export async function handleInit({
  name,
  id,
  src,
  roomList
}) {
    // 此处逻辑需要抽离复用
  socket.emit('login', {name, id, ...env});
  roomList.forEach(item => {
    const obj = {
      id,
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
  id,
  roomid
}) {
  socket.emit('read_messages', {id, roomid});
}