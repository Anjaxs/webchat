import axios from 'axios';
import Toast from "@components/Toast";
import Alert from "@components/Alert";
import {getItem, clear} from '@utils/localStorage';

const baseURL = '';


const instance = axios.create();

instance.defaults.timeout = 30000; // 所有接口30s超时

// 请求统一处理
instance.interceptors.request.use(async config => {
  if (config.url && config.url.charAt(0) === '/') {
    config.url = `${baseURL}${config.url}`;
  }

  config.headers.authorization = `Bearer ${getItem('token')}`;

  return config;
}, error => Promise.reject(error));

// 对返回的内容做统一处理
instance.interceptors.response.use(response => {
  if (response.status === 200) {
    return response;
  }
  return Promise.reject(response);
}, error => {
  console.log('api/axios.js:30, error.response');
  console.log(error.response)
  if (error && error.response) {
    if (error.response.status == 401) {
      Alert({
        content: '请先登录'
      });
      clear();
    } else if (error.response.status == 422) {
      // 业务逻辑错误
      Alert({
        content: error.response.data.msg
      });
    } else if (error.response.status == 404) {
      Toast({
        content: '访问的页面不存在~',
        timeout: 2000,
        background: "#f44336"
      });
    } else {
      Toast({
        content: '网络异常，请检查你的网络。',
        timeout: 2000,
        background: "#f44336"
      });
    }
  } else {
    Toast({
      content: '未知错误。',
      timeout: 2000,
      background: "#f44336"
    });
  }
  return Promise.reject(error);
});

export default instance;
