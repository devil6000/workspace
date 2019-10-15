var app = getApp()

Page({

  /**
   * 页面的初始数据
   */
  data: {
    //会员类型
    userType: '',
    //会员信息
    memberInfo: '',
    is_firing_page: 0,
    setting_set: '',
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
    slide: {
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
    },
    //是否授权
    is_empower: 0
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

  //显示引导页
  showLoading: function(){
    var that = this
    var timestamp = Date.parse(new Date())
    var sto_setting_set = wx.getStorageSync('setting_set')
    var at_show_firing_page = wx.getStorageSync('at_show_firing_page')
    var expire = sto_setting_set.expire ? sto_setting_set.expire : 0
    //显示引导页
    if((!at_show_firing_page && !that.data.is_firing_page) && (sto_setting_set.data && expire <= timestamp)){
      //如果时间超过预设超时并且未展示过则重新展示   两个条件同时满足才显示 否则默认为显示过了
      sto_setting_set.expire = timestamp + sto_setting_set.data.rolling_interval_time * 60 * 1000
      wx.setStorageSync('setting_set', sto_setting_set)
      that.setData({
        is_firing_page: 1
      })
      that.drawProgressbg()
      that.startProgress(that.data.setting_set.rolling_time ? that.data.setting_set.rolling_time : sto_setting_set.data.rolling_time)
    }else{
      //证明显示过了 (不满足显示条件则直接设置为已显示 防止点击个人中心页面展示此页面)
      wx.setStorageSync('at_show_firing_page', 1)
      that.setData({
        is_firing_page: 0
      })

      //判断是否授权
      wx.getSetting({
        success: function(res){
          if (res.authSetting['scope.userInfo']){
            that.setData({
              is_firing_page: 0,
              is_empower: 1
            })
          }else{
            that.setData({
              is_firing_page: 0,
              is_empower: 0
            })
          }
        }
      })
    }
  },

  //画progress底部背景
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

  //开始progress
  startProgress: function (time) {
    // 设置倒计时 定时器 每100毫秒执行一次，计数器count+1 ,耗时6秒绘一圈
    this.countTimer = setInterval(() => {
      if (this.data.count <= time * 100) {
        /* 绘制彩色圆环进度条  
        注意此处 传参 step 取值范围是0到2，
        所以 计数器 最大值 60 对应 2 做处理，计数器count=60的时候step=2
        */

        this.drawCircle(this.data.count / (time * 100 / 2))

        this.data.count++;
        var progresscount = this.data.count / 100;
        this.setData({
          progress_txt: progresscount < 10 && time >= 10 ? ('0' + progresscount.toFixed(0)) : progresscount.toFixed(0)
        });
      } else {
        this.setData({
          count: 0
        });
        console.log('加载完成');
        clearInterval(this.countTimer);
      }
    }, 10)
  },

  hideLoading: function(data){
    var that = this
    var at_show_firing_page = wx.getStorageSync('at_show_firing_page') ? wx.getStorageSync('at_show_firing_page') : 0
    //如果显示延迟
    if(!at_show_firing_page){
      setTimeout(function(){
        that.setData({
          is_firing_page: 0
        })
        wx.setStorageSync('at_show_firing_page', 1) //证明显示过了
        //跳转到登录页面
        if (data.complete && typeof data.complete == 'function'){
          data.complete()
        }
      }, (that.data.setting_set.rolling_time * 1000))
    }else{
      //跳转到登录页面
      if (data.complete && typeof data.complete == 'function') {
        data.complete()
      }
    }
  },


  // socket收到的信息回调
  onSocketMessageCallback: function (msg) {
    console.log('收到消息回调', msg)
  },

  //获取图片真实宽度
  imageLoad: function(e){
    var imgWidth = e.detail.width,imgHeight = e.detail.height,ratio = imgWidth / imgHeight
    var viewHeight = 750 / ratio
    var imgHeight = viewHeight
    var imgHeights = this.data.imgheights
    //把每一张图片的对应的高度记录到数组里
    imgHeights[e.target.dataset.index] = imgHeight
    this.setData({
      imgheights: imgHeights
    })
  },

  bindchange: function (e) {
    this.setData({ current: e.detail.current })
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
  calling: function () {
    wx.makePhoneCall({
      phoneNumber: '18017295129  ',
    })
  },
  getEmpower: function(){
    var t = this, e = setInterval(function () {
      wx.getSetting({
        success: function (n) {
          var a = n.authSetting["scope.userInfo"];
          a && (clearInterval(e), wx.setStorageSync('at_empower', 1), t.setData({is_empower: 1}), t.onShow());
        }
      });
    }, 1e3);
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this
    if(options){
      wx.setStorageSync('introducer', options.introducer)
    }
    var timestamp = Date.parse(new Date())
    var setting = wx.getStorageSync('setting_set')
    var at_show_firing_page = wx.getStorageSync('at_show_firing_page')
    
    //判断是否获取授权
    wx.getSetting({
      success: function (res) {
        if (res.authSetting['scope.UserInfo']) {
          that.setData({
            is_empower: 1
          })
          wx.setStorageSync('at_empower', 1)
        }else{
          that.setData({
            is_empower: 0
          })
        }
      }
    })

    if(setting.data && setting.expire > timestamp){
      that.setData({
        setting_set: setting.data
      })
      that.showLoading()
    }else{
      //获取系统设置,并且缓存3分钟,引导页
      app.util.request({
        'url': 'entry/wxapp/getsetting',
        'cachetime': 180,
        'data': that.data.location,
        success: function(res){
          that.setData({
            setting_set: res.data.data
          })
          var sto_setting_set = wx.getStorageSync('setting_set')
          sto_setting_set = sto_setting_set ? sto_setting_set : {}
          sto_setting_set.data = res.data.data
          wx.setStorageSync('setting_set', sto_setting_set)
          that.showLoading()
        }
      })
    }
    //这里的信息只是作为暂时维持页面显示用
    var userType = wx.getStorageSync('userType')
    if(userType){
      if(userType == 'issuer'){
        that.setData({
          userType: userType
        })
      }else {
        that.setData({
          userType: 'worker'
        })
      }
    }else{
      that.setData({
        userType: 'index'
      })
    }
    app.util.footer(that)
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
    
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    var that = this
    //用户验证
    app.user_verify()
    var userType = ''
    //防止多次跳转
    this.pageLoading = !1
    //判断是否授权
    wx.getSetting({
      success: function(res){
        if(res.authSetting['scope.userInfo']){
          app.util.getUserInfo(function(userInfo){
            if (userInfo && typeof (userInfo) != "undefined" && typeof (userInfo) != "string"){
              app.memberInfo = userInfo
              that.setData({
                memberInfo: userInfo
              })
              app.util.request({
                url: 'entry/wxapp/getcenter',
                data: {},
                method: 'POST',
                success: function(res){
                  var info = res.data.data.info
                  that.setData({
                    memberInfo: info
                  })
                  wx.setStorageSync('user_info', info)
                  if(!userType){
                    if(info.type == 1){
                      wx.setStorageSync('userType', 'worker')
                      app.globalData.userType = 'worker'
                      that.setData({
                        userType: 'worker'
                      })
                    }else{
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
                  })
                },
                fail: function(res){
                  //这里关闭引导页 (不管这个有没有被打开都执行)
                  that.hideLoading({
                    complete: ''
                  })
                  wx.setStorageSync('userType', '')
                  that.onLoad()
                  return false
                }
              })
            }else{
              //这里关闭引导页 跳转到登录页面
              that.hideLoading({
                complete: ''
                /*
                complete: function () {
                  if (!that.pageLoading) {
                    that.pageLoading = !0
                    wx.navigateTo({
                      url: '/jujiwuliu/pages/common/auth/index',
                    })
                  }
                }
                */
              })
            }
          })
        }else{
          //这里关闭引导页 跳转到登录页面
          that.hideLoading({
            complete: ''
          })
        }
      }
    })
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
    
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
    // 页面销毁时关闭连接
    app.webSocket.closeSocket();
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
    
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    
  }
})