/**
 * Created by Administrator on 2017/4/17.
 */
import Vue from 'vue';
import Vuex from 'vuex';
import url from '@api/server.js';
import {setItem, getItem} from '@utils/localStorage';
import {ROBOT_NAME, ROBOT_URL} from '@const/index';
import findLast from 'lodash/findLast';
import findLastIndex from 'lodash/findLastIndex';

Vue.use(Vuex);

const store = new Vuex.Store({
  state: {
    // 个人信息
    userInfo: {
      src: getItem('src'),
      userid: getItem('userid'),
      id: getItem('id'),
      token: getItem('token'),
    },
    lookUserInfo: {
    },
    // 朋友列表
    friendList: [],
    isDiscount: false,
    isLogin: false,
    // 存放聊天记录 key - []
    roomdetail: {},
    roomUsers: {},
    // 存放机器人开场白
    robotmsg: [
    {
      id: 1,
      username: ROBOT_NAME,
      src: ROBOT_URL,
      msg: '您好，请问有什么能帮助到您的？',
      sessionId: '',
    },
    ],
    unRead: {
      room1: 0,
      room2: 0
    },
    hotUserList: [],
    vipUserList: [],
    searchUserList: [],
    // svg
    svgmodal: null,
    // 是否启动tab

    emojiShow: false,

    theme: "#2196f3"
  },
  getters: {
    // getUsers: state => state.roomdetail.users,
    getInfos: state => state.roomdetail.infos,
    getRobotMsg: state => state.robotmsg,
    getEmoji: state => state.emojiShow
  },
  mutations: {
    setDiscount(state, value) {
      state.isDiscount = value;
    },
    setUnread(state, value) {
      for (let i in value) {
        state.unRead[i] = value[i];
      }
    },
    setLoginState(state, value) {
      state.isLogin = value;
    },
    setUserInfo(state, data) {
      const {type, value} = data;
      // 如果是 key - value 形式，为单项设置
      if(value) {
        setItem(type, value);
        state.userInfo[type] = value;
      } else {
        const info = Object.keys(data);
        // 清空所有
        if(info.length == 0) {
          Object.keys(state.userInfo).map(item => {
            state.userInfo[item] = '';
            setItem(item, '');
          });
        } else {
          // 多数据一次性设置
          info.map(item => {
            state.userInfo[item] = data[item];
            setItem(item, data[item]);
          });
        }
      }
    },
    setEmoji(state, data) {
      state.emojiShow = data;
    },
    setSvgModal(state, data) {
      state.svgmodal = data;
    },
    delRoomDetailImg(state, data) {
      const { roomid, clientId } = data;
      const clientIndex = findLastIndex(state.roomdetail[roomid], {clientId});
      state.roomdetail[roomid].splice(clientIndex, clientIndex + 1);
    },
    setRoomDetailStatus(state, data) {
      const { roomid, status, clientId, typeList, newClientId } = data;
      const clientItem = findLast(state.roomdetail[roomid], {clientId});
      typeList.map(item => {
        clientItem[item] = data[item];
      })
      // 重试
      if(newClientId) {
        clientItem.clientId = newClientId;
      }
      console.log('setRoomDetailStatus clientItem', clientItem);
    },
    // 发送消息后，本地消息数组追加消息
    setRoomDetailInfosAfter(state, data) {
      const { roomid, msgs } = data;
      if(!state.roomdetail[roomid]) {
        state.roomdetail[roomid] = [];
      }
      state.roomdetail[roomid].push(...msgs);
    },
    setRoomDetailInfosBeforeNoRefresh(state, {data, roomid}) {
      const list = state.roomdetail[roomid] || [];
      const newData = data.concat(list);
      state.roomdetail[roomid] = newData;
    },
    setRoomDetailInfosBefore(state, {data, roomid}) {
      const list = state.roomdetail[roomid] || [];
      const newData = data.concat(list);
      state.roomdetail = {
        ...(state.roomdetail),
        [roomid]: newData
      }
    },
    setRoomDetailInfos(state, {data, roomid}) {
      state.roomdetail.infos = data;
    },
    setUsers(state, data) {
      const { roomid, onlineUsers } = data;
      const roomUsers = []
      const list = onlineUsers;
      state.roomUsers = {
        ...(state.roomUsers),
        [roomid]: list
      }
    },
    setRobotMsg(state, data) {
      state.robotmsg.push(data);
    },
    setLookUserInfo(state, data) {
      state.lookUserInfo = data;
    },
    setFriendList(state, data) {
      state.friendList = data;
    },
    sethotUserList(state, data) {
      state.hotUserList = data;
    },
    setvipUserList(state, data) {
      state.vipUserList = data;
    },
    setSearchList(state, data) {
      state.searchUserList = data;
    },
    setAllmsg(state, data) {
      state.roomdetail = data;
    }
  },
  actions: {
    async getSearch({state, commit}, data) {
      const res = await url.getSearch(data);
      if(res.status === 200) {
        commit('setSearchList', res.data.data)
      }
    },
    async getvipuser({state, commit}, data) {
      const res = await url.getvipuser(data);
      if(res.status === 200) {
        commit('setvipUserList', res.data.data)
      }
    },
    async getHostUserList({state, commit}, data) {
      const res = await url.getHostUserList(data);
      if(res.status === 200) {
        commit('sethotUserList', res.data.data)
      }
    },
    async addFriend({}, data) {
      const res = await url.postAddFriend(data);
      return res;
    },
    async uploadAvatar({}, data) {
      const res = await url.postUploadAvatar(data);
      return res.data;
    },
    async uploadImg({}, data) {
      try {
        const res = await url.postUploadFile(data);
        if (res) {
          if (res.status === 200) {
            return {
              data: res.data,
              code: 0,
            }
          } else {
            return {
              data: res.data.data,
              code: 500,
            }
          }
        }
      } catch(e) {
        return {
          data: '服务端异常,重新发送',
          code: 500,
        }
      }
    },
    async registerSubmit({}, data) {
      const res = await url.RegisterUser(data);
      if (res.status === 200) {
        return {
          status: 'success',
          data: res.data
        };
      }
      return {
        status: 'fail',
        data: res.data
      };
    },
    async loginSubmit({}, data) {
      const res = await url.loginUser(data);
      if (res.status === 200) {
        return {
          status: 'success',
          data: res.data
        };
      }
      return {
        status: 'fail',
        data: res.data
      };
    },
    async logoutSubmit({state, commit}) {
      const res = await url.logoutUser();
      if (res.status === 200) {
        commit("setUserInfo", {})
        commit("setUnread", {
          room1: 0,
          room2: 0
        })
      }
    },
    async getUserInfo({state, commit}, data) {
      const res = await url.getUserInfo(data);
      if(res.status === 200) {
        commit('setLookUserInfo', res.data.data);
      }
    },
    async postListFriend({state, commit}, data) {
      const res = await url.postListFriend(data);
      if(res.status === 200) {
        commit('setFriendList', res.data.data);
      }
    },
    async getRoomHistory({state, commit}, data) {
      const res = await url.getRoomHistory(data);
      if(res.status === 200) {
        const result = res.data.data;
        if(result) {
          commit('setAllmsg', result);
        }
      }
    },
    // 获取房间所有历史消息
    async getAllMessHistory({state, commit}, data) {
      try {
        const res = await url.RoomHistoryAll(data);
        if (res.status === 200) {
          const list = res.data.list.reverse();
          if(data.msgid) {
            commit('setRoomDetailInfosBeforeNoRefresh', {
              data: list,
              roomid: data.roomid
            });
          } else {
            commit('setRoomDetailInfosBefore', {
              data: list,
              roomid: data.roomid
            });
          }

          return {
            data: list
          }
        }
      } catch(e) {
        console.log('store/index.js getAllMessHistory', e);
      }
    },
    async getRobatMess({commit}, data) {
      const username = ROBOT_NAME;
      const src = ROBOT_URL;
      const res = await url.getRobotMessage(data);
      if (res) {
        const msgs = res.data.result.messages || [];
        const msg = msgs.length > 0 ? msgs[0].text.content : '';
        const sessionId = res.data.result.sessionId || '';
        commit('setRobotMsg', {msg, username, src, sessionId});
      }
    }
  }
});
export default store;
