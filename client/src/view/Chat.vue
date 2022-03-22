<template>
  <div>
    <div class="chat-container">
      <div class="title">
        <mu-appbar color="primary">
          <mu-button icon slot="left" @click="goback">
            <mu-icon value="chevron_left"></mu-icon>
          </mu-button>
          <div class="center">
            {{roomType === 'group' ? `聊天(${Object.keys(roomUsers[roomid] || {}).length})` : friendName}}
          </div>
          <mu-button v-if="roomType === 'group'" icon slot="right" @click="openSimpleDialog">
            <mu-icon value="people"></mu-icon>
          </mu-button>
          <mu-button v-else icon slot="right"></mu-button>
        </mu-appbar>
      </div>
      <!-- <div class="notice" v-if="noticeList.length > 0" :class="[noticeBar ? 'notice-hidden' : '']">
        <div class="notice-container">
          <div class="notice-li" v-for="(item, key) in noticeList" :key="key">
            <a :href="item.href">{{key + 1}}. {{item.title}}</a>
          </div>
        </div>
        <div class="notice-tool-bar" @click="handleNotice">
          {{noticeBar ? '显示通知' : '关闭通知'}}
        </div>
      </div> -->
      <div class="chat-inner">
        <div class="chat-container" @scroll="bindScroll">
          <div v-if="(roomdetail[roomid] || []).length === 0" class="chat-no-people">暂无消息,赶紧来占个沙发～</div>
          <div v-if="(roomdetail[roomid] || []).length !== 0 && isloading" class="chat-loading">
            <div class="lds-css ng-scope">
              <div class="lds-rolling">
                <div>
                </div>
              </div>
            </div>
          </div>
          <div v-if="isEnd && (roomdetail[roomid] || []).length !== 0" class="chat-top">到顶啦~</div>
          <Message
            v-for="(obj, index) in (roomdetail[roomid] || [])"
            @avatarClick="handleInfo"
            @flexTouch="hadnleTouch"
            @retry="handleRetry"
            :key="obj.id"
            :is-self="obj.user_id === userid"
            :id="obj.id"
            :name="obj.user.name"
            :head="obj.user.avatar"
            :msg="obj.msg"
            :clientId="obj.clientId"
            :roomid="obj.room_id"
            :img="obj.img"
            :loading="obj.loading"
            :status="obj.status"
            :mytime="obj.created_at"
            :obj="obj"
            :container="container"
            :isLast="roomdetail[roomid].length - 1 === index"
            ></Message>
          <div class="clear"></div>
        </div>
      </div>
      <div class="bottom">
        <div class="functions">
          <div class="fun-li" @click="imgupload">
            <i class="icon iconfont icon-camera"></i>
          </div>
          <div class="fun-li emoji">
            <i class="icon iconfont icon-emoji"></i>
            <div class="emoji-content" v-show="getEmoji">
              <div class="emoji-tabs">
                <div class="emoji-container" ref="emoji">
                  <div class="emoji-block" :style="{width: Math.ceil(emoji.people.length / 5) * 48 + 'px'}">
                    <span v-for="(item, index) in emoji.people" :key="index">{{item}}</span>
                  </div>
                  <div class="emoji-block" :style="{width: Math.ceil(emoji.nature.length / 5) * 48 + 'px'}">
                    <span v-for="(item, index) in emoji.nature" :key="index">{{item}}</span>
                  </div>
                  <div class="emoji-block" :style="{width: Math.ceil(emoji.items.length / 5) * 48 + 'px'}">
                    <span v-for="(item, index) in emoji.items" :key="index">{{item}}</span>
                  </div>
                  <div class="emoji-block" :style="{width: Math.ceil(emoji.place.length / 5) * 48 + 'px'}">
                    <span v-for="(item, index) in emoji.place" :key="index">{{item}}</span>
                  </div>
                  <div class="emoji-block" :style="{width: Math.ceil(emoji.single.length / 5) * 48 + 'px'}">
                    <span v-for="(item, index) in emoji.single" :key="index">{{item}}</span>
                  </div>
                </div>
                <div class="tab">
                  <!-- <a href="#hot"><span>热门</span></a>
                  <a href="#people"><span>人物</span></a> -->
                </div>
              </div>
            </div>
          </div>
          <div class="fun-li" @click="handleGithub">
            <i class="icon iconfont icon-wenti"></i>
          </div>
        </div>
        <div class="chat">
          <div class="input" @keyup.enter="submess">
            <input type="text" v-model="chatValue">
          </div>
          <mu-button class="demo-raised-button" color="primary" @click="submess">发送</mu-button>
        </div>
        <input id="inputFile" name='inputFile' type='file' multiple='mutiple' accept="image/gif,image/jpeg,image/png,image/webp,image/jpg;capture=camera"
                style="display: none" @change="fileup">
      </div>
    </div>
  </div>
</template>

<script type="text/ecmascript-6" scoped>
  import {mapGetters, mapState} from 'vuex';
  import {inHTMLData} from 'xss-filters-es6';
  import emoji from '@utils/emoji';
  import {setItem, getItem} from '@utils/localStorage';
  import {queryString} from '@utils/queryString';
  import Message from '@components/Message';
  import loading from '@components/loading/loading';
  import Alert from '@components/Alert';
  import debounce from 'lodash/debounce';
  import ios from '@utils/ios';
  import { v4 as uuid } from 'uuid';
  import { readMessages, sendMessage } from '../socket-handle';

  let isMore = false;

  export default {
    name: 'Chat',
    data() {
      const notice = getItem('notice') || {};
      const {noticeBar, noticeVersion} = notice;
      return {
        isloading: false,
        roomid: '',
        roomType: 'group',
        container: {},
        chatValue: '',
        emoji: emoji,
        openSimple: false,
        noticeBar: !!noticeBar,
        noticeList: [],
        noticeVersion: noticeVersion || '20181222',
        isEnd: false,
        to: '',
        from: '',
        friendName: '',
      };
    },
    async created() {
      const roomId = queryString(window.location.href, 'roomId');
      const roomType = queryString(window.location.href, 'type');
      const to = queryString(window.location.href, 'to');
      const from = queryString(window.location.href, 'from');
      const friendName = queryString(window.location.href, 'friendName');
      this.roomid = roomId;
      this.roomType = roomType;
      this.to = to;
      this.from = from;
      this.friendName = friendName;
      if (!roomId) {
        this.$router.push({path: '/'});
      }
      if (!this.token) {
        // 防止未登录
        this.$router.push({path: '/login'});
      }
      // const res = await url.getNotice();
      // this.noticeList = res.data.noticeList;
      // if (res.data.version !== res.data.version) {
      //   this.noticeBar = false;
      // }
      // this.noticeVersion = res.data.version;
    },
    async mounted() {
      loading.show({
        marginTop: '56px',
        background: '#f1f5f8'
      });
      // 微信 回弹 bug
      ios();
      this.container = document.querySelector('.chat-container');
      this.isloading = true;
      if(!this.roomdetail[this.roomid]) {
        await this.getRoomMessage();
      }
      this.isloading = false;
      loading.hide();

      this.bindEmoji();
    },
    methods: {
      handleRetry(obj) {
        if(obj.img) {
          Alert({
            content: '图片暂时不支持重新发送'
          })
          return;
        }
        const clientId = uuid();
        this.$store.commit('setRoomDetailStatus', {
          clientId: obj.clientId,
          newClientId: clientId,
          roomid: obj.roomid,
          status: 'loading',
          typeList: ['status']
        })
        sendMessage({
          ...obj,
          clientId,
          status: 'loading'
        });
      },
      handleInfo(item) {
        this.$router.push({ path: "/persionDetail", query: { id: item.id } });
      },
      hadnleTouch(data) {
        this.chatValue = this.chatValue + data;
      },
      bindScroll: debounce(async function (e) {
        console.log('bindScroll e.target.scrollTop', e.target.scrollTop);
        if (e.target.scrollTop >= 0 && e.target.scrollTop < 100) {
          this.handleScroll();
        }
      }, 30),
      async handleScroll() {
        if(!isMore && !this.isEnd) {
          this.isloading = true;
          isMore = true;
          await this.getRoomMessage();
          isMore = false;
          this.isloading = false;
        }
      },
      bindEmoji() {
        this.$refs.emoji.addEventListener('click', (e) => {
          var target = e.target || e.srcElement;
          if (!!target && target.tagName.toLowerCase() === 'span') {
            this.chatValue = this.chatValue + target.innerHTML;
          }
          e.stopPropagation();
        });
      },
      async getRoomMessage() {
        const data = {
          roomid: this.roomid
        };
        if(this.roomdetail[this.roomid] && this.roomdetail[this.roomid].length > 0) {
          const id = this.roomdetail[this.roomid][0].id;
          data.msgid = id;
        }
        try {
          const result = await this.$store.dispatch('getAllMessHistory', data);
          console.log('getAllMessHistory result', result);
          if(!result.data.length) {
            this.isEnd = true;
          }
          // 当前消息id没有，说明读取的是最新消息
          if(!data.id) {
            readMessages({token:this.userInfo.token, roomid:this.roomid})
          }
        } catch(e) {
          console.log('view/Chat.vue:getRoomMessage', e)
        }
      },
      handleNotice() {
        this.noticeBar = !this.noticeBar;
        setItem('notice', {
          noticeBar: this.noticeBar,
          noticeVersion: this.noticeVersion
        });
      },
      openSimpleDialog () {
        this.$router.push({ path: "/groupDetail", query: { roomId: this.roomid} });
      },
      handleGithub() {
        Alert({
          content: 'https://github.com/anjaxs/webchat'
        });
      },
      goback () {
        this.$router.isBack = true;
        this.$router.goBack();
      },
      setLog() {
        // 版本更新日志
      },
      async fileup() {
        const that = this;
        const file1 = document.getElementById('inputFile').files[0];
        if (file1) {
          const formdata = new window.FormData();
          formdata.append('file', file1);
          const fr = new window.FileReader();
          fr.onload = function () {
            const img = new Image();
            img.src = fr.result;
            img.onload = async function() {
              const obj = {
                user: {
                  name: that.userInfo.userid,
                  id: that.userInfo.id,
                  avatar: that.userInfo.src,
                },
                img: `${fr.result}?width=${img.width}&height=${img.height}`,
                msg: '',
                to: that.to,
                from: that.from,
                roomType: that.roomType,
                room_id: that.roomid,
                user_id: that.userInfo.id,
                created_at: new Date(),
                type: 'img',
                clientId: uuid()
              };

               // 传递消息信息
              that.$store.commit('setRoomDetailInfosAfter', {
                roomid: that.roomid,
                msgs: [{
                  ...obj,
                  status: 'loading',
                  loading: 5,
                }]
              });

              const imgurl = await that.$store.dispatch('uploadImg', formdata);
              console.log(imgurl);
              if(imgurl.code == 500) {
                Alert({
                  content: imgurl.data
                })
                that.$store.commit('delRoomDetailImg', {
                  roomid: that.roomid,
                  clientId: obj.clientId
                })
                return;
              }
              obj.img = `${imgurl.data.url}?width=${img.width}&height=${img.height}`;

              sendMessage(obj);
            }

          };
          fr.readAsDataURL(file1);
          this.$nextTick(() => {
            this.container.scrollTop = this.container.scrollHeight;
          });
        } else {
          console.log('必须有文件');
        }
      },
      imgupload() {
        const file = document.getElementById('inputFile');
        file.click();
      },
      submess() {
        // 判断发送信息是否为空
        if (this.chatValue !== '') {
          if (this.chatValue.length > 200) {
            Alert({
              content: '请输入100字以内'
            });
            return;
          }
          const msg = inHTMLData(this.chatValue); // 防止xss

          const obj = {
            user: {
              name: this.userInfo.userid,
              id: this.userInfo.id,
              avatar: this.userInfo.src,
            },
            img: '',
            msg,
            to: this.to,
            from: this.from,
            roomType: this.roomType,
            room_id: this.roomid,
            user_id: this.userInfo.id,
            created_at: new Date(),
            type: 'text',
            clientId: uuid()
          };
          console.log('send message obj:', obj);
          // 传递消息信息
          this.$store.commit('setRoomDetailInfosAfter', {
            roomid: this.roomid,
            msgs: [{
              ...obj,
              status: 'loading'
            }]
          });

          sendMessage(obj)
          this.chatValue = '';
        } else {
          Alert({
            content: '内容不能为空'
          });
        }
      }
    },
    computed: {
      ...mapGetters([
        'getEmoji',
      ]),
      ...mapState([
        'isbind',
        'roomdetail',
        'roomUsers'
      ]),
      ...mapState({
        userInfo: state => state.userInfo,
        token: state => state.userInfo.token,
        username: state => state.userInfo.userid,
        userid: state => state.userInfo.id,
        src: state => state.userInfo.src
      })
    },
    components: {
      Message
    }
  };
</script>

<style lang="stylus" rel="stylesheet/stylus" src="./Chat.styl" scoped></style>
