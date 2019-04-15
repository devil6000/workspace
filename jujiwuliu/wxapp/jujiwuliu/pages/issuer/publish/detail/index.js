


// jujiwuliu/pages/issuer/publish/detail/index.js
var dateTimePicker = require('../../../../resource/js/dateTimePicker.js');
var app = getApp()

Page({

  /**
   * 页面的初始数据
   */
  data: {
    siteinfo: app.siteInfo,
    issuer:[],
    make_money_model:0,
    make_money_check:0,
    close_order_show:1,
    apply_money_status: 0,  //申请订单退款状态0可申请，1申请中，2已退款
    apply_bond_status: 0, //申请保证金退款状态0可申请，1申请中，2已退款
    modalPayBtns: false,  //是否显示支付页面
  },
  //下面是提交打款莫泰框
  make_money_model_hide:function(){
    this.setData({
      make_money_model:0,
    })
  },
  make_money_model_show(){
    this.setData({
      make_money_model:1,
    })
  },
  make_money_check:function(e){
    this.setData({
      make_money_check: e.detail.value
    })
    
  },
  submit_make_money:function(e){
    var that = this;
    var issuer_id = this.data.options.issuer_id;
    var make_money_check = this.data.make_money_check;
    var make_money_date = e.detail.value.makemoneytime
    app.util.request({
      'url': 'entry/wxapp/MakeMoney',
      //   'cachetime': '180',
      'data': { issuer_id: issuer_id, make_money_check: make_money_check, make_money_date: make_money_date},
      success: function (res) {
        if (res.data.errno == 0) {
          wx.showModal({
            title: '温馨提示',
            content: '结算成功!',
            showCancel: false,
            success: function () {
              wx.navigateTo({
                url: '../list/index'
              });
              that.setData({
                receipt_model_show: 0,
                make_money_model: 0,
                close_order_show: 1,
              })
            }
          })
        }
        console.log(res)
      }
    });
  },
  close_order: function(){
    var that = this;
    var issuer_id = this.data.options.issuer_id;
    wx.showModal({
      title: '温馨提示',
      content: '确认完成订单?',
      success:function(res){
        if(res.confirm){
          app.util.request({
            url: 'entry/wxapp/CloseOperation',
            data: { rid: issuer_id},
            success: function(res){
              if(res.data.errno == 0){
                wx.redirectTo({
                  url: '../list/index',
                })
              }
            }
          })
        }
      }
    })
  },
  //提交打款结束
  //  点击日期组件确定事件
  getDateTime: function (e) {
    console.log(e.detail.value)
    var obj = dateTimePicker.dateTimePicker(this.data.startYear, this.data.endYear);
    var dateTime = e.detail.value;
    this.setData({
      dateTime: e.detail.value,
      starttime: obj.dateTimeArray[0][dateTime[0]] + '-' + obj.dateTimeArray[1][dateTime[1]] + '-' + obj.dateTimeArray[2][dateTime[2]] + ' ' + obj.dateTimeArray[3][dateTime[3]] + ':' + obj.dateTimeArray[4][dateTime[4]] + ':' + obj.dateTimeArray[5][dateTime[5]],
    });
  },
  getDateTimeColumn: function (e) {
    var obj = dateTimePicker.dateTimePicker(this.data.startYear, this.data.endYear);
    var arr = this.data.dateTime,
      dateArr = this.data.dateTimeArray;
    arr[e.detail.column] = e.detail.value;
    dateArr[2] = dateTimePicker.getMonthDay(
      dateArr[0][arr[0]],
      dateArr[1][arr[1]]
    );
    // var dateTime = obj.dateTime;
    this.setData({
      dateTimeArray: dateArr,
      //starttime: obj.dateTimeArray[0][dateTime[0]] + '-' + obj.dateTimeArray[1][dateTime[1]] + '-' + obj.dateTimeArray[2][dateTime[2]] + ' ' + obj.dateTimeArray[3][dateTime[3]] + ':' + obj.dateTimeArray[4][dateTime[4]] + ':' + obj.dateTimeArray[5][dateTime[5]],
      dateTime: arr
    });
  },
  sexPickerChange: function (e) {
    this.setData({
      sexIndex: e.detail.value
    });
  },

    submit_invoice:function(){
      var that=this;
        var issuer_id = this.data.options.issuer_id;
      var check_firmid = this.data.check_firmid;

      console.log(check_firmid);
        app.util.request({
            'url': 'entry/wxapp/Postinvoice',
            //   'cachetime': '180',
          'data': { issuer_id: issuer_id, check_firmid: check_firmid},
            success: function (res) {
              if (res.data.errno==0){
                wx.showModal({
                  title: '温馨提示',
                  content: '申请成功！',
                  showCancel:false,
                  success: function () {
                    that.setData({
                      receipt_model_show: 0,
                    })
                    that.onShow();
                  }
                })
              }
                console.log(res)
            }
        });
    },
  firm_check:function(e){
      console.log(e.detail.value);
      this.setData({
          check_firmid: e.detail.value
      })
  },
    receipt_model_show:function(){
      if(this.data.firms==0){
        wx.showModal({
          title: '温馨提示',
          content: '请先添加开票单位！',
          cancelText:'取消',
          confirmText:'去添加',
          success: function (res) {
            if (res.confirm==true){
              wx.navigateTo({
                url: '/jujiwuliu/pages/issuer/receipt/apply/index'
              });
            }
          }
        })
        return;
      }
        this.setData({
            receipt_model_show: 1,
        })
    },
    receipt_model_hide:function(){
        this.setData({
            receipt_model_show:0,
        })
    },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    console.log(options)
      //获取系统设置 并且缓存3分钟 引导页
      app.util.request({
          'url': 'entry/wxapp/getsetting',
        //   'cachetime': '180',
          'data': that.data.location,
          success: function (res) {
              that.setData({
                  setting_set: res.data.data
              })

          }
      });

      

    if (!(options)){
      wx.showModal({
        title: '温馨提示',
        content: '未查询到此信息！',
        success:function(){
          
        }
      })
    }
    that.setData({
      options: options
    })
    
    app.util.footer(that);
  },
    url_order(e){
        var that=this;
        var orderid = e.currentTarget.dataset.orderid;
        var rid = that.data.options.issuer_id; 
        if (!orderid || !rid){
            return;
        }
        wx.navigateTo({
            url: '/jujiwuliu/pages/issuer/details/index?id=' + orderid + '&rid='+rid
        });
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
    var that=this;
    app.util.request({
      'url': 'entry/wxapp/getissusr',
      'cachetime': '0',
      'data': { 'id':that.data.options.issuer_id },
      success: function (res) {
        console.log(res)
        var close_order_show = (res.data.data.status == 4) ? 0 : 1
        that.setData({
          issuer: res.data.data,
          apply_money_status: res.data.data.apply_refund,
          apply_bond_status: res.data.data.apply_refund_bond
        })

      }
    })
    app.util.request({
      url: 'entry/wxapp/GetFirmList',
      success: function (res) {
        console.log(res.data.data);
        that.setData({
          firms: res.data.data ? res.data.data : 0
        })
      }
    })




    //下面是页面初始化
    var obj = dateTimePicker.dateTimePicker(that.data.startYear, that.data.endYear);
    var lastArray = obj.dateTimeArray;
    var lastTime = obj.dateTime;
    var dateTime = obj.dateTime;

    that.setData({
      dateTime: obj.dateTime,
      dateTimeArray: obj.dateTimeArray,
      starttime: obj.dateTimeArray[0][dateTime[0]] + '-' + obj.dateTimeArray[1][dateTime[1]] + '-' + obj.dateTimeArray[2][dateTime[2]] + ' ' + obj.dateTimeArray[3][dateTime[3]] + ':' + obj.dateTimeArray[4][dateTime[4]] + ':' + obj.dateTimeArray[5][dateTime[5]],
    });
  },
    cancel_ord:function(){
        var that=this;
        var issuer_id= that.data.options.issuer_id ;
        if (!issuer_id){
            wx.showModal({
                title: '温馨提示',
                content: '操作失败,请关闭小程序从新进入！',
                success: function () {

                }
            })
            return false;
        }
        wx.showModal({
            title: '温馨提示',
          content: (that.data.setting_set.issue_cancel_time ? that.data.setting_set.issue_cancel_time:0)+'分钟内可免责取消订单！',
            confirmText: "坚持退款",
            cancelText: "取消",
            success: function (res) {
                if (!res.cancel) {
                    //取消订单
                    // issuer_id
                    var formid = wx.getStorageSync("formId")
                    app.util.request({
                        'url': 'entry/wxapp/IssusrCancel',
                        'cachetime': '0',
                        'data': { 'id': issuer_id, 'formid':formid },
                        success: function (res) {
                            console.log(res)
                            wx.showModal({
                                title: '温馨提示',
                                content: '操作成功！',
                                success: function () {
                                  wx.navigateTo({
                                    url: '/jujiwuliu/pages/issuer/publish/detail/index?issuer_id=' + that.data.issuer.id
                                  });
                                  //that.onLoad(that.data.options)
                                    // that.onShow();
                                    /*
                                    var pages = getCurrentPages();
                                    var prevPage = pages[pages.length - 2];  //上一个页面
                                    prevPage.onLoad(prevPage.options)
                                    */
                                }
                            })
                        }
                    })
                }
            }
        })

    },
  /**
   * 申请退款
   */  
  apply_money: function(){
    var that = this
    var formid = wx.getStorageSync('formId')
    app.util.request({
      url: 'entry/wxapp/applymoney',
      data: { id: that.data.options.issuer_id, formid: formid},
      method: 'POST',
      success: function(res){
        if(res.data.errno == 0){
          wx.showModal({
            title: '温馨提示',
            content: '申请退款成功',
            showCancel: false
          })
          that.setData({
            apply_money_status: 1
          })
        }
      }
    })
  },
  /**
   * 申请保证金退款
   */
  apply_bond: function(){
    var that = this
    var formid = wx.getStorageSync('formId')
    app.util.request({
      url: 'entry/wxapp/applybond',
      data: { id: that.data.options.issuer_id, formid: formid },
      method: 'POST',
      success: function (res) {
        if (res.data.errno == 0) {
          wx.showModal({
            title: '温馨提示',
            content: '申请退款成功',
            showCancel: false
          })
          that.setData({
            apply_bond_status: 1
          })
        }
      }
    })
  },
  pay_status: function(){
    this.setData({
      modalPayBtns: true
    })
  },
  hide_paybtns: function(){
    this.setData({
      modalPayBtns: false
    })
  },
  check_pay: function (e) {
    var that = this;
    var pay_data = that.data.pay_data;
    var order_id = that.data.order_id;
    that.setData({
      modalPayBtns: !1
    })
    if (e.currentTarget.dataset.paytype == 1) {  //微信支付
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
    if (e.currentTarget.dataset.paytype == 2 && that.data.credit_enough == 1) {  //余额支付
      app.util.request({
        url: 'entry/wxapp/createWorkerPay',
        data: { id: order_id },
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