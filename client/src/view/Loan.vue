<template>
  <div class="hello">
    <div class="">
      <mu-appbar>
        <mu-button icon slot="left" >
        </mu-button>
        <div class="title">webchat</div>
        <mu-button icon slot="right">
          <mu-icon value="expand_more"></mu-icon>
        </mu-button>
      </mu-appbar>
    </div>
    <div class="chat-list">
      <mu-paper>
        <mu-list>
          <mu-sub-header>最近聊天记录</mu-sub-header>
          <mu-list-item avatar button :ripple="true" @click="chatwindow('room1')">
            <mu-list-item-action>
              <div class="avatar">
                <span class="tip" v-if="unRead1!==0">{{unRead1 > 99 ? '99+' : unRead1}}</span>
                <Avatar :src="house1" size="small"></Avatar>
              </div>
            </mu-list-item-action>
            <mu-list-item-content>
              <mu-list-item-title>聊天室1</mu-list-item-title>
              <mu-list-item-sub-title>{{getTailMsg('room1')}}</mu-list-item-sub-title>
            </mu-list-item-content>
            <mu-list-item-action>
              <mu-icon value="chat_bubble"></mu-icon>
            </mu-list-item-action>
          </mu-list-item>
          <mu-list-item avatar button :ripple="true" @click="chatwindow('room2')">
            <mu-list-item-action>
              <div class="avatar">
                <span class="tip" v-if="unRead2!==0">{{unRead2 > 99 ? '99+' : unRead2}}</span>
                <Avatar :src="house2" size="small"></Avatar>
              </div>
            </mu-list-item-action>
            <mu-list-item-content>
              <mu-list-item-title>聊天室2</mu-list-item-title>
              <mu-list-item-sub-title>{{getTailMsg('room2')}}</mu-list-item-sub-title>
            </mu-list-item-content>
            <mu-list-item-action>
              <mu-icon value="chat_bubble"></mu-icon>
            </mu-list-item-action>
          </mu-list-item>
        </mu-list>
        <mu-divider/>
        <mu-list>
          <mu-sub-header>客服</mu-sub-header>
          <mu-list-item avatar button :ripple="true" @click="chatRobot('')">
            <mu-list-item-action>
              <mu-avatar class="avatar">
                <svg t="1647309471807" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2980" width="40" height="40"><path d="M935 448h-15.7c-4.1-40.5-14.2-79.9-30.1-117.6-20.6-48.8-50.2-92.6-87.8-130.2S720 133 671.3 112.4C620.8 91 567.1 80.2 511.9 80.2c-55.3 0-108.9 10.8-159.4 32.2-48.8 20.6-92.6 50.2-130.2 87.8-30.2 30.2-55.2 64.4-74.5 101.9l-24-23.6-39 53.6 41.3 20c-3.8 10.6-7.1 21.3-10 32.1H77.4l9.4 63.2 18.4-6.3c-0.3 2.3-0.5 4.6-0.8 6.9H89c-13.8 0-25 11.2-25 25v78c0 13.8 11.2 25 25 25h30c13.7 0 25-11.2 25-25v-78c0-7.3-3.2-13.9-8.2-18.4 0.8-8.4 1.8-16.8 3.2-25.1l31-10.6c-4.8 22.9-7.3 46.7-7.3 71 0 15.6 1 30.9 3 45.9l3.7 5.9c34.3 55.1 82.6 100.8 139.5 132.1 44.6 24.5 93.9 39.6 144.5 44.6 5.7-8.6 15.6-14.4 26.6-14.4h64.1c17.6 0 32 14.4 32 32v0.1c0 17.6-14.4 32-32 32H480c-12.4 0-23.3-7.2-28.5-17.6-55.4-5.3-109.2-21.7-158-48.5-39.9-21.9-75.9-50.4-106.4-83.9 49.2 123.8 167.5 212.6 307.4 218.7L469.7 894s190.8-91.2 194.2-92.9c114.3-56.8 193-174.7 193-311 0-191.7-155.4-347.1-347.1-347.1-140.8 0-262 83.9-316.4 204.5L171.6 326c18.4-38.1 43-72.7 73.2-102.9 34.7-34.7 75.1-61.9 120-80.9 46.5-19.7 96-29.7 147-29.7s100.4 10 147 29.7c45 19 85.3 46.2 120 80.9s61.9 75.1 80.9 120c15.2 35.8 24.6 73.4 28.1 112-4.8 4.6-7.8 11-7.8 18.1v78c0 13.8 11.2 25 25 25h30c13.7 0 25-11.2 25-25v-78c0-14-11.2-25.2-25-25.2zM509.8 332.4c172 0 311.5 61.3 311.5 137s-139.5 137-311.5 137-311.5-61.3-311.5-137 139.5-137 311.5-137z m-330.6 51.7h-30c1.8-6 3.7-12 5.7-18l26.1 12.6-1.8 5.4z" fill="#1296db" p-id="2981"></path><path d="M314.5 507.1l98.6-18.9c13.7-2.6 16.1-21.3 3.5-27.2-32.3-15.2-76.2-32.7-93.1-24.9-17.9 8.3-24 36.2-26.1 55.4-1.1 9.6 7.6 17.4 17.1 15.6zM617.9 488.2l98.6 18.9c9.5 1.8 18.2-6 17.1-15.7-2.1-19.2-8.3-47.1-26.1-55.4-16.9-7.9-60.7 9.7-93.1 24.9-12.6 6-10.2 24.6 3.5 27.3z" fill="#1296db" p-id="2982"></path></svg>
              </mu-avatar>
            </mu-list-item-action>
            <mu-list-item-title>客服大白(微信群，作者联系方式，找我)</mu-list-item-title>
            <mu-list-item-action>
              <mu-icon value="chat_bubble"></mu-icon>
            </mu-list-item-action>
          </mu-list-item>
        </mu-list>
        <mu-list>
          <mu-sub-header>好友</mu-sub-header>
          <mu-list-item avatar button :ripple="true" @click="chatSingle(item.friendId._id, item.friendId.name)" v-for="item in friendList" :key="item._id">
            <mu-list-item-action>
              <mu-avatar class="avatar">
                <img :src="item.friendId.src">
              </mu-avatar>
            </mu-list-item-action>
            <mu-list-item-content>
              <mu-list-item-title>{{item.friendId.name}}</mu-list-item-title>
              <mu-list-item-sub-title>{{getSingleTailMsg(item.friendId._id)}}</mu-list-item-sub-title>
            </mu-list-item-content>
            <mu-list-item-action>
              <mu-icon value="chat_bubble"></mu-icon>
            </mu-list-item-action>
          </mu-list-item>
        </mu-list>
      </mu-paper>
    </div>
    <Bottom></Bottom>
    <!-- <p style="text-align: center;position: absolute; bottom:0;width: 100%;">©2020 蓝色的秋风&nbsp&nbsp&nbsp<a href="http://beian.miit.gov.cn" target="_blank">浙ICP备16040413号-1</a></p> -->
  </div>
</template>

<script>
import Confirm from "@components/Confirm";
import Bottom from "@components/Bottom";
import Avatar from "@components/Avatar";
import { mapState } from "vuex";
import env from '@utils/env';
import { sort } from '@utils/tools';
import { ROBOT_URL, HOST_URL1, HOST_URL2 } from "@const/index";
import socket from "../socket";

export default {
  name: 'Loan',
  data() {
    return {
      house1: HOST_URL1,
      house2: HOST_URL2,
      robot: ROBOT_URL
    };
  },
  async mounted() {
    // 只全局监听一次
    if(this.userInfo.id) {
      this.$store.dispatch('postListFriend', {selfId: this.userInfo.id})
    }
  },
  methods: {
    getSingleTailMsg(friendId) {
      const userId = this.userInfo.id;
      const roomID = sort(userId, friendId);
      return this.getTailMsg(roomID);
    },
    getTailMsg(roomid) {
      const roomData = this.roomdetail[roomid] || [];
      if(roomData.length === 0) {
        return '暂无消息';
      }
      const lastMsg = roomData[roomData.length-1];
      const { username, msg, img } = lastMsg;

      if(img) {
        return `${username}说: [图片]`;
      } else {
        const content = `${username}说: ${msg}`;
        if(content.length > 15) {
          return `${username}说: ${msg.slice(0, 7)}...`;
        }
        return content;
      }
    },
    async chatwindow(roomID) {
      if (!this.userInfo.token) {
        const res = await Confirm({
          title: "提示",
          content: "聊天请先登录，但是你可以查看聊天记录哦~"
        });
        if (res === "submit") {
          this.$router.push({ path: "login" });
        }
        return;
      }
      this.$router.push({ path: "/chat", query: { roomId: roomID, type: 'group' } });
    },
    async chatSingle(friendId, friendName) {
      const userId = this.userInfo.id;
      const roomID = sort(userId, friendId);
      this.$router.push({ path: "/chat", query: { roomId: roomID, from: userId,to: friendId, type: 'single', friendName } });
    },
    chatRobot() {
      this.$router.push({ path: "/robot" });
    }
  },
  computed: {
    ...mapState({
      roomdetail: state => state.roomdetail,
      username: state => state.userInfo.userid,
      userid: state => state.userInfo.id,
      src: state => state.userInfo.src,
      isLogin: state => state.isLogin,
      unRead1: state => state.unRead.room1,
      unRead2: state => state.unRead.room2,
      userInfo: state => state.userInfo,
      friendList: state => state.friendList
    })
  },
  components: {
    Bottom,
    Avatar
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="stylus" rel="stylesheet/stylus">
.title {
  text-align: center;
}
.chat-list {
  overflow-y: scroll;
  height: calc(100% - 133px);
}
.avatar {
  position: relative;

  .tip {
    position: absolute;
    right: -5px;
    top: -8px;
    padding: 0px 5px;
    border-radius: 10px;
    line-height: 20px;
    text-align: center;
    background: #ff2a2a;
    color: #fff;
    font-size: 12px;
  }

  .mu-avatar {
    img {
      border-radius: 5px;
    }
  }
}
</style>
