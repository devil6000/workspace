var app = getApp()

Page({
  data:{
    userType: '',
    memberInfo: '',
    locationModelShow: 0,
    is_firing_page: 0,
    setting_set:'',
    //图片
    //是否采用衔接滑动  
    circular: true,
    //是否显示画板指示点  
    indicatorDots: false,
    //选中点的颜色  
    indicatorcolor: "#000",
    //是否竖直  
    vertical: false,
    //是否自动切换  
    autoplay: true,
    //自动切换的间隔
    interval: 2500,
    //滑动动画时长毫秒  
    duration: 100,
    //所有图片的高度  
    imgheights: [],
    //图片宽度 
    imgwidth: 750,
    //默认  
    current: 0,
    slide : {
    },
    //控制progress
    count: 0, // 设置 计数器 初始为0
    countTimer: null,// 设置 定时器
    progress_txt: '加载中...',// 提示文字
    distanceData: {
      modelShowDistance: !1,
      distanceArr: [
        { name: '10公里', value: '10' },
        { name: '20公里', value: '20' },
        { name: '无限制', value: '-1' }
      ]
    }
  },
  //发布方
  issuer: function (e) {
    wx.navigateTo({
      url: '/jujiwuliu/pages/issuer/bind/index'
    });
  },
  //搬运工
  worker: function (e) {
    wx.navigateTo({
      url: '/jujiwuliu/pages/worker/bind/index'
    });
  },
  onLoad:function(options){
    var that = this
    
    // //获取地理位置
    // app.util.getLocation({
    //   cachetime: 180,//3分钟更新一次
    //   success: function (data) {
    //     console.log(111)
    //   },
    //   fail: function (res) {
    //     that.setData({
    //       locationModelShow: 1,
    //     });
    //   }
    // });

    if (options){
      wx.setStorageSync('introducer', options.introducer)
    }
    // wx.setStorageSync('introducer', app.util.base64_encode('4'))
    var timestamp = Date.parse(new Date());
    var setting = wx.getStorageSync('setting_set')
    var at_show_firing_page = wx.getStorageSync('at_show_firing_page')
    
    if (setting.data && setting.expire > timestamp) {
      that.setData({
        setting_set: setting.data
      })
      // //绘制背景
      // that.drawProgressbg();
      // //开始progress
      // that.startProgress(that.data.setting_set.rolling_time);
      that.showLoading();
    } else {
      //获取系统设置 并且缓存3分钟 引导页
      app.util.request({
        'url': 'entry/wxapp/getsetting',
        'cachetime': '180',
        'data': that.data.location,
        success: function (res) {
          
          // app.util.message(res.data.message);
          that.setData({
            setting_set: res.data.data
          })
          // wx.setStorageSync('setting_set', { 'data': res.data.data, 'expire': timestamp + res.data.data.rolling_interval_time * 1000 * 60 })
          var sto_setting_set = wx.getStorageSync('setting_set')
          sto_setting_set = sto_setting_set ? sto_setting_set:{};
          sto_setting_set.data = res.data.data;
          wx.setStorageSync('setting_set', sto_setting_set)

          // //绘制背景
          // that.drawProgressbg();
          // //开始progress
          // that.startProgress(that.data.setting_set.rolling_time);
          // console.log(res.data.data.rolling_diagram)
          that.showLoading();
          
        }
      });
    }


    //这里的信息只是作为暂时维持页面显示用
    // wx.setStorageSync('userType', '');
    var userType = wx.getStorageSync('userType')
    if (userType) {
      if (userType == 'issuer') {
        that.setData({
          userType: 'issuer'
        })
      } else {
        that.setData({
          userType: 'worker'
        })
      }
    }else{
      that.setData({
        userType: 'index'
      })
    }
    app.util.footer(that);
    
    // //发起websocket

  }, onUnload: function (options) {
    // 页面销毁时关闭连接
    app.webSocket.closeSocket();
  },
  // socket收到的信息回调
  onSocketMessageCallback: function (msg) {
    console.log('收到消息回调', msg)
  },
  imagecheck:function(){//图片上传
    var that=this;
    wx.chooseImage({ //选择图片
      count:2,
      success:function($file){
        var FileSystemManager=wx.getFileSystemManager();
        FileSystemManager.readFile({
          filePath: $file['tempFilePaths'][0],//文件路径
            encoding:'base64',
          success:function(res){
            console.log(res)
            console.log($file)

            var filename = $file['tempFilePaths'][0].split('.');
            filename=filename[filename.length - 1];//文件后缀
// 

            console.log(filename)
            var obj = {};
            obj.type = 'upload';
            obj.scene = 'fabu';
            obj.filedata = res.data; 
            obj.suffix = filename;
            obj.uniacid = 1;
            obj.uid = 11;
            obj.nickname = 2121;
            app.webSocket.sendSocketMessage({ msg: JSON.stringify(obj) });//发送数据
           
          },fail:function(res){
            console.log(res)
          }

        })

        // wx.openDocument({
        //   filePath: $file['tempFilePaths'][0],
        //   success: function (res) {
        //     console.log(res)
        //     console.log('ok');
        //   },fail:function(res){
        //     console.log(res)
            
        //   }
        // });
      }
    })
  },
  showLoading:function(){
    var that=this;
    var timestamp = Date.parse(new Date());
    var sto_setting_set = wx.getStorageSync('setting_set')
    var setting = wx.getStorageSync('setting_set')
    var at_show_firing_page = wx.getStorageSync('at_show_firing_page')
      setting.expire = setting.expire ? setting.expire:0;
      
    //显示引导页
    // if ((!at_show_firing_page && !that.data.is_firing_page) && (setting.data && setting.expire <= timestamp)) {//如果时间超过预设超时并且未展示过则重新展示
    if ((!at_show_firing_page && !that.data.is_firing_page) && (setting.data && setting.expire <= timestamp)) {//如果时间超过预设超时并且未展示过则重新展示   两个条件同时满足才显示 否则默认为显示过了
      sto_setting_set.expire = timestamp + sto_setting_set.data.rolling_interval_time * 1000 * 60;
     wx.setStorageSync('setting_set', sto_setting_set)
      //上面这里很重要 只有展示了才加时间 否则就直接展示

      that.setData({
        is_firing_page: 1
      })
      //绘制背景
      that.drawProgressbg();
      //开始progress
      that.startProgress(that.data.setting_set.rolling_time ? that.data.setting_set.rolling_time : setting.data.rolling_time);
    } else {
        wx.setStorageSync('at_show_firing_page', 1)//证明显示过了 (不满足显示条件则直接设置为已显示 防止点击个人中心页面展示此页面)
      that.setData({
        is_firing_page: 0
      })
    }
  },
  hideLoading:function(data){
    var that=this;
    var at_show_firing_page = wx.getStorageSync('at_show_firing_page') ? wx.getStorageSync('at_show_firing_page'):0;
    //如果有显示则延时
    if (!at_show_firing_page){
      setTimeout(function () {
        that.setData({
          is_firing_page: false
        })
        wx.setStorageSync('at_show_firing_page', 1)//证明显示过了
        //获取地理位置
        app.util.getLocation({
          cachetime: 180,//3分钟更新一次
          success: function (data) {
          },
          fail: function (res) {
            that.setData({
              locationModelShow: 1,
            });
          }
        });
        //跳转到登陆授权页面
        if (data.complete && typeof data.complete == 'function') {
          data.complete();
        }
      }, (that.data.setting_set.rolling_time * 1000))
    }else{//否则直接过
      //获取地理位置
      app.util.getLocation({
        cachetime: 180,//3分钟更新一次
        success: function (data) {
        },
        fail: function (res) {
          that.setData({
            locationModelShow: 1,
          });
        }
      });
      //跳转到登陆授权页面
      if (data.complete && typeof data.complete == 'function') {
        data.complete();
      }
    }
    
  },
  touchHideLoading:function(){
    var that=this;
      that.setData({
        is_firing_page: false
      })
      // wx.setStorageSync('at_show_firing_page', 1)//证明显示过了
      //获取地理位置
      app.util.getLocation({
        cachetime: 180,//3分钟更新一次
        success: function (data) {
        },
        fail: function (res) {
          that.setData({
            locationModelShow: 1,
          });
        }
      });
      //跳转到登陆授权页面
      if (!that.pageLoading && !that.data.memberInfo){
        that.pageLoading = !0;
        wx.navigateTo({
          url: '/jujiwuliu/pages/common/auth/index'
        })
      }
  },
  isEmptyObject: function (e) {
      var t
      for(t in e)
      return !1
      return !0
    },

  handleAtText : function (obj) {
    if (obj.text == '' || !obj.at || this.isEmptyObject(obj.at)) {
      return
    }
    obj.at.each(function (key, val) {
      obj.text = obj.text.replace('@' + val + ': ', '')
    });
    return obj
  },

  onShow:function(){
    // 页面显示
    var that = this
      //验证用户
    app.user_verify();

    var userType = '';
    //var userType = wx.getStorageSync('userType') //不读缓存每次重新获取
    this.pageLoading = !1;//防止多次跳转
    app.util.getUserInfo(function (userInfo) {
      //获取到用户信息后再执行下面的操作
      console.log(userInfo)
      
      if (userInfo && typeof (userInfo) != "undefined" && typeof (userInfo) != "string" ) {
        app.memberInfo = userInfo;
        that.setData({
          memberInfo: userInfo,
        });
        console.log(app.memberInfo )
        app.util.request({//获取会员信息
          url: 'entry/wxapp/getcenter',
          data: {},
          method: "POST",
          success: function (res) {
            
            var info = res.data.data.info;
            that.setData({
              memberInfo: info
            })
            
            wx.setStorageSync('user_info', info)
            if (!userType){
              if (info.type == 1) {
                wx.setStorageSync('userType', 'worker')
                app.globalData.userType = 'worker'
                that.setData({
                  userType: 'worker'
                })
              } else {
                wx.setStorageSync('userType', 'issuer')
                app.globalData.userType = 'issuer'
                that.setData({
                  userType: 'issuer'
                })
              }
            }
            //初始化底部导航
            app.util.footer(that);

            //这里关闭引导页
            that.hideLoading({
              complete: ''
            });
            
          },
          fail: function (res) {
            //这里关闭引导页 (不管这个有没有被打开都执行)
            that.hideLoading({
              complete:''
            });
           
            wx.setStorageSync('userType', '')
            that.onLoad()
            return false;
          }
        })
      } else {
         //这里关闭引导页
        that.hideLoading({
          complete:function(){
            if (!that.pageLoading) {
              that.pageLoading = !0;
              wx.navigateTo({
                url: '/jujiwuliu/pages/common/auth/index'
              })
            }
          }
          
        });
       
      }
    });

  
  },
  imageLoad: function (e) {//获取图片真实宽度  
  
    var imgwidth = e.detail.width,
      imgheight = e.detail.height,
      //宽高比  
      ratio = imgwidth / imgheight;

    //计算的高度值  
    var viewHeight = 750 / ratio;
    var imgheight = viewHeight;
    var imgheights = this.data.imgheights;
    //把每一张图片的对应的高度记录到数组里  
    imgheights[e.target.dataset.index] = imgheight;
    this.setData({
      imgheights: imgheights
    })
  },
  bindchange: function (e) {
    this.setData({ current: e.detail.current })
  },
  confirmclick: function () {
    this.setData({
      locationModelShow: !1
    }), wx.openSetting({
      success: function (t) { }
    });
  },
  onHide:function(){
    // 页面隐藏
  },
  onUnload:function(){
    // 页面关闭
  },
  onShareAppMessage:function(){
    //转发事件 带上用户id （加密） 
    var user_info = wx.getStorageSync('user_info');
    var setting = wx.getStorageSync('setting_set');

    return { 
      title: setting.data.share_title,
      path: '/jujiwuliu/pages/index/index?introducer=' + app.util.base64_encode(user_info.id),
      // imageUrl:'https://wx.qlogo.cn/mmopen/vi_32/IYXncFLbvfZdjygNiaNyyoQn6yOI8icXZJEYTdibjhfkJKaIUlwTgLe9NZeFsRuJ1Mia7E2wRZXsiaEvy1C00Abiad6Q/132',
    }
  },/**
  * 画progress底部背景
  */
  drawProgressbg: function () {
    // 使用 wx.createContext 获取绘图上下文 context
    var ctx = wx.createCanvasContext('canvasProgressbg')
    // 设置圆环的宽度
    ctx.setLineWidth(2);
    // 设置圆环的颜色
    ctx.setStrokeStyle('#000000');
    // 设置圆环端点的形状
    ctx.setLineCap('round')
    //开始一个新的路径
    ctx.beginPath();
    //设置一个原点(110,110)，半径为100的圆的路径到当前路径
    ctx.arc(30, 30, 20, 0, 2 * Math.PI, false);
    //对当前路径进行描边
    ctx.stroke();
    //开始绘制
    ctx.draw();
  },

  /**
   * 画progress进度
   */
  drawCircle: function (step) {
    // 使用 wx.createContext 获取绘图上下文 context
    var context = wx.createCanvasContext('canvasProgress');
    // 设置圆环的宽度
    context.setLineWidth(2);
    // 设置圆环的颜色
    context.setStrokeStyle('#FBE6C7');
    // 设置圆环端点的形状
    context.setLineCap('round')
    //开始一个新的路径
    context.beginPath();
    //参数step 为绘制的圆环周长，从0到2为一周 。 -Math.PI / 2 将起始角设在12点钟位置 ，结束角 通过改变 step 的值确定
    context.arc(30, 30, 20, -Math.PI / 2, step * Math.PI - Math.PI / 2, false);
    //对当前路径进行描边
    context.stroke();
    //开始绘制
    context.draw()
  },

  /**
   * 开始progress
   */
  startProgress: function (time) {
    
    // 设置倒计时 定时器 每100毫秒执行一次，计数器count+1 ,耗时6秒绘一圈
    this.countTimer = setInterval(() => {
      if (this.data.count <= time*100) {
        /* 绘制彩色圆环进度条  
        注意此处 传参 step 取值范围是0到2，
        所以 计数器 最大值 60 对应 2 做处理，计数器count=60的时候step=2
        */
        
        this.drawCircle(this.data.count / (time * 100 / 2))
       
        this.data.count++;
        var progresscount=this.data.count / 100;
        this.setData({
          // progress_txt: progresscount < 10 && time >= 10 ? ('0' + progresscount.toFixed(2)) : progresscount.toFixed(2)
          progress_txt: progresscount < 10 && time>=10 ? ('0' + progresscount.toFixed(0)) : progresscount.toFixed(0)
        });
      } else {
        this.setData({
          count: 0
        });
        console.log('加载完成');
        clearInterval(this.countTimer);
        //this.startProgress();
      }
    }, 10)
  },
  setDistance: function () {
    var distance = wx.getStorageSync('distanceType')
    if (distance == '') {
      distance = 10
    }
    var t
    let distanceArr = this.data.distanceData.distanceArr
    for (let i = 0; i < distanceArr.length; i++) {
      if (distanceArr[i].value == distance) {
        distanceArr[i].checked = true
      } else {
        distanceArr[i].checked = false
      }
    }
    this.setData({
      'distanceData.modelShowDistance': !0,
      'distanceData.distanceArr': distanceArr
    })
  },
  hide_distance: function () {
    this.setData({
      'distanceData.modelShowDistance': !1
    })
  },
  distanceChange: function (e) {
    this.setData({
      distanceType: e.detail.value
    })
  },
  confirm_distance: function () {
    var type = this.data.distanceType
    wx.setStorageSync('distanceType', type)
    this.setData({
      'distanceData.modelShowDistance': !1
    })
    this.onShow(this.options)
  },
  calling: function(){
    wx.makePhoneCall({
      phoneNumber: '18017295129  ',
    })
  },
})