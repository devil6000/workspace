// jujiwuliu/pages/worker/details/index.js
var app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    siteinfo: app.siteInfo,
    data: {},
    //图片
    slide: {
      0: '../../../resource/images/banyungong.png',
      1: '../../../resource/images/banyungong.png',
      2: '../../../resource/images/banyungong.png',
    },
    //是否采用衔接滑动
    circular: true,
    //是否显示画板指示点
    indicatorDots: true,
    //选中点的颜色
    indicatorcolor: "#000",//被css冲突了
    //是否竖直
    vertical: false,
    //是否自动切换
    autoplay: true,
    //滑动动画时长毫秒
    duration: 1000,
    //所有图片的高度
    imgheights: [500],//不填写则默认获取图片高度（比较耗资源）
    //图片宽度
    imgwidth: 750,
    //默认
    current: 0,
    //到场标识
    present: false,
    //是否已申请打款标志
    applystatus: false,
    paytype: 0, //支付类型，1微信，2余额
    payData: {
      modalPayBtns: !1, //支付选择
      bond: 0, //保证金
      credit_enough: 0 //余额
    }
  },
  payTypeChange: function(e){
    console.log(e.detail.value)
    this.setData({
      paytype: e.detail.value
    })
  },

  takeOrder: function(e){
    var that = this
    var formid = e.detail.formId;
    app.util.request({
      url: 'entry/wxapp/getworkerpay',
      data: { formid: formid, id: that.data.data.id},
      method: 'POST',
      success: function(res){
        var data = res.data.data
        that.setData({
          'payData.bond': data.bond,
          'payData.modalPayBtns': !0,
          'payData.credit_enough': data.credit_enough,
          /*
          bond: data.bond,
          modalPayBtns: !0,
          credit_enough: data.credit_enough,
          */
          pay_data: data.pay_data,
          deposit_sn: data.ordersn
        })
      },fail: function(res){
        var message = res.data.message;
        wx.showModal({
          title: '获取失败',
          content: message,
          showCancel: false
        });
        return false;
      }
    })
  },

  /*
  takeOrder: function(){
    var that = this
    var formid = wx.getStorageSync("formId")
    wx.showModal({
      title: '温馨提示',
      content: '接单需支付需支付保证金',
      success: function(res){
        if(res.confirm == true){
          app.util.request({
            url: 'entry/wxapp/gettakeOrder',
            data: {
              id: that.data.data.id,
              formid: formid
            },
            method: "POST",
            success: function (res) {
              var data = res.data.data
              console.log(res);
              wx.showModal({
                title: '温馨提示',
                content: '接单成功',
                showCancel: false,
                success: function (res) {
                  that.setData({
                    modalPayBtns: !data.paystatus,
                    credit_enough: data.credit_enough,
                    order_id: data.id,
                    pay_data: data.pay_data,
                    bond: data.bond
                  })
                }
              });
            },
            fail: function (res) {
              var message = res.data.message;
              wx.showModal({
                title: '获取失败',
                content: message,
                showCancel: false
              });
              return false;
            }
          })
        }
      }
    })
  },
  */
  check_pay: function(e){
    var that = this;
    var pay_data = that.data.pay_data;
    var ordersn = that.data.deposit_sn;
    var paytype = that.data.paytype
    if(paytype == 0){
      wx.showModal({
        title: '温馨提示',
        content: '请选择支付方式',
        showCancel: false
      })
    }else{
      that.setData({
        'payData.modalPayBtns': !1
        /*modalPayBtns: !1*/
      })

      if (paytype == 1) {//微信支付
        wx.requestPayment({
          timeStamp: pay_data.timeStamp,
          nonceStr: pay_data.nonceStr,
          package: pay_data.package,
          signType: 'MD5',
          paySign: pay_data.paySign,
          success: function (res) {
            wx.showModal({
              title: '温馨提示',
              content: '支付成功',
              showCancel: false,
              success: function (res) {
                that.onLoad(that.options)
              }
            })
          }
        })
      }

      if (paytype == 2 && that.data.payData.credit_enough == 1){
        app.util.request({
          url: 'entry/wxapp/createWorkerPay',
          data: { ordersn: ordersn },
          method: 'POST',
          success: function (res) {
            wx.showModal({
              title: '温馨提示',
              content: '支付成功',
              showCancel: false,
              success: function (res) {
                that.onLoad(that.options)
              }
            })
          },
          fail: function (res) {
            wx.showModal({
              title: '温馨提示',
              content: res.data.message,
              showCancel: false
            })
          }
        })
      }
    }
  },
  hide_paybtns: function(){
    this.setData({
      'payData.modalPayBtns': !1
    })
  },
  applyrefundbond: function(e){
    var that = this
    var formid = e.detail.formId;
    app.util.request({
      url: 'entry/wxapp/refundWorkerBond',
      data: { id: that.data.data.orderid, formid: formid},
      method: 'POST',
      success: function(res){
        wx.showModal({
          title: '温馨提示',
          content: '申请成功',
          showCancel: false
        })

        that.onLoad(that.options)
      },
      fail: function(res){
        wx.showModal({
          title: '温馨提示',
          content: res.data.message,
          showCancel:false
        })
      }
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this
    //获取地理位置
    app.util.getLocation({
      cachetime: 180,//3分钟更新一次
      success: function (data) {
        console.log(111)
      },
      fail: function (res) {
        console.log(111)

        that.setData({
          locationModelShow: 1,
        });
      }
    });
    var cachedata = wx.getStorageSync('getLocation');
    that.setData({
      options: options,
    });

    app.util.request({
      url: 'entry/wxapp/getlistdetails',
      data: {
        rid: options.id,
        lat: cachedata.data.lat,
        lng: cachedata.data.lng,
      },
      method: "POST",
      success: function (res) {
        console.log(res);
        var data = res.data.data;
        that.setData({
          data: data,
          slide:data.images,
          'payData.modalPayBtns': !data.deposit_paystatus,
          /*modalPayBtns: !data.deposit_paystatus,*/
          present: data.distance <= 15 ? true : false,
          applystatus: data.apply == 1 ? true : false
        })
      },
      fail: function (res) {
        var message = res.data.message;
        wx.showModal({
          title: '获取失败',
          content: message,
          showCancel: false
        });
        return false;
      }
    })
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
    //获取用户数据
    app.util.request({
      url: 'entry/wxapp/getcenter',
      data: {},
      method: "POST",
      success: function (res) {
        console.log(res);
        var info = res.data.data.info;
        that.setData({
          rest: info.status
        })
      },
      fail: function (res) {
        console.log(res)
        return false;
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

  },

  imageLoad: function (e) {//轮播图高度自适应
    //获取图片真实宽度
    var imgwidth = e.detail.width,
      imgheight = e.detail.height,
      //宽高比
      ratio = imgwidth / imgheight;
    //计算的高度值

    var viewHeight = 750 / ratio;
    var imgheight = viewHeight
    var imgheights = this.data.imgheights
    //把每一张图片的高度记录到数组里
    imgheights.push(imgheight)
    this.setData({
      imgheights: imgheights,
    })
  },
  fulfilOrder:function(e){
    var that = this
    var cachedata = wx.getStorageSync('getLocation');
    var formid = e.detail.formId;
    //获取用户数据
    app.util.request({
      url: 'entry/wxapp/setapply',
      data: { id: that.data.data.orderid, lat: cachedata.data.lat, lng: cachedata.data.lng, formid: formid},
      method: "POST",
      success: function (res) {
        console.log(res);
        wx.showModal({
          title: '温馨提示',
          content: '提交成功，若发布方3天未审核将自动打款！',
          showCancel: false,
          success:function(res){
            wx.navigateTo({
              url: '/jujiwuliu/pages/worker/details/index?id=' + that.options.id,
            })
          }
        })
      },
      fail: function (res) {
        console.log(res)
        wx.showModal({
          title: '温馨提示',
          content: res.data.message,
          showCancel:false
        })
      }
    })
  },
  cancelOrder:function(e){
    var that = this
    var timestamp = Date.parse(new Date())
    timestamp = timestamp / 1000  
    var diff = (timestamp - that.data.data.createtime)
    var overtime = that.data.data.overtime
    var msg = '是否取消订单'
    if(overtime && (diff >= (overtime * 60))){
      msg = '订单已超过有效期，取消订单将扣除保证金'
    }
    var formid = e.detail.formId;
    wx.showModal({
      title: '温馨提示',
      content: msg,
      success: function(res){
        if(res.confirm){
          //直接取消
          app.util.request({
            url: 'entry/wxapp/cancelorder',
            data: { id: that.data.data.orderid,formid: formid},
            method: "POST",
            success: function (res) {
              var page_opt = getCurrentPages();
              var prev_page = page_opt[page_opt.length - 2];
              prev_page.setData({
                nav_opt: 1
              })
              //给上一页传值
              wx.navigateBack();//返回上一页
            },
            fail: function (res) {
              wx.showModal({
                title: '取消失败',
                content: res.data.message,
              })
            }
          })
        }
      }
    })
  },
  startOperation: function(e){
    var that = this
    var cachedata = wx.getStorageSync('getLocation');
    var formid = e.detail.formId;
    wx.showModal({
      title: '温馨提示',
      content: '是否正式开工',
      success: function(res){
        if(res.confirm){
          app.util.request({
            url: 'entry/wxapp/startoperation',
            data: { id: that.data.data.orderid, lat: cachedata.data.lat, lng: cachedata.data.lng, formid: formid },
            method: "POST",
            success: function (res) {
              var page_opt = getCurrentPages();
              var prev_page = page_opt[page_opt.length - 2];
              prev_page.setData({
                nav_opt: 1
              })
              //给上一页传值
              wx.navigateBack();//返回上一页
            },
            fail: function (res) {
              wx.showModal({
                title: '开工失败',
                content: res.data.message,
                showCancel: false
              })
            }
          })
        }
      }
    })
  },
  closeOperation: function(res){
    var that = this
    wx.showModal({
      title: '温馨提示',
      content: '是否确认完成工作',
      success:function(res){
        if(res.confirm){
          app.util.request({
            url: 'entry/wxapp/closeoperation',
            data: {id: that.data.data.orderid},
            method: "POST",
            success: function(res){
              var page_opt = getCurrentPages();
              var prev_page = page_opt[page_opt.length - 2];
              prev_page.setData({
                nav_opt: 1
              })
              //给上一页传值
              wx.navigateBack();//返回上一页
            },
            fail: function(res){
              wx.showModal({
                title: '完工失败',
                content: res.data.message,
                showCancel: false
              })
            }
          })
        }
      }
    })
  },
  bindchange: function (e) {//轮播图无限滚动
    this.setData({ current: e.detail.current })
  },
  callPhone: function(){
    wx.makePhoneCall({
      phoneNumber: this.data.data.release_mobile
    })
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