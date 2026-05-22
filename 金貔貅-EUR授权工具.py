#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
金貔貅-EUR 授权码生成器
XOR加密 + Base64编码 生成离线授权码
"""

import base64
import datetime
import sys
import os
import tkinter as tk
from tkinter import ttk


# ========== 核心加密逻辑 ==========

def generate_license(account: str, expiry_date: str) -> str:
    """
    生成授权码
    account: 交易账号字符串，如 "165447734"
    expiry_date: 过期日期，格式 "YYYYMMDD"，如 "20260621"
    """
    key = b"JPXEUR2026GridEA!@#"
    plain = f"{account}|{expiry_date}".encode('ascii')

    # XOR加密：明文按字节与密钥循环异或
    encrypted = bytearray()
    for i, b in enumerate(plain):
        encrypted.append(b ^ key[i % len(key)])

    # Base64编码
    return base64.b64encode(bytes(encrypted)).decode('ascii')


# ========== GUI应用 ==========

class LicenseGeneratorApp:
    # 颜色方案
    BG_COLOR = "#1a1f2e"
    INPUT_BG = "#2a3040"
    INPUT_FG = "#ffffff"
    LABEL_FG = "#b0b8c8"
    INFO_FG = "#40c060"
    STATUS_FG = "#6090c0"
    ERROR_FG = "#e04040"
    BTN_GOLD_BG = "#c8a832"
    BTN_GOLD_FG = "#1a1f2e"
    BTN_BLUE_BG = "#3070c0"
    BTN_BLUE_FG = "#ffffff"
    BORDER_COLOR = "#3a4050"

    def __init__(self, root: tk.Tk):
        self.root = root
        self.root.title("金貔貅-EUR - 授权码生成器")
        self.root.configure(bg=self.BG_COLOR)
        self.root.resizable(False, False)

        # 尝试设置图标
        self._set_icon()

        # 居中窗口
        self.root.geometry("420x480")
        self.root.update_idletasks()
        w = self.root.winfo_width()
        h = self.root.winfo_height()
        x = (self.root.winfo_screenwidth() // 2) - (w // 2)
        y = (self.root.winfo_screenheight() // 2) - (h // 2)
        self.root.geometry(f"+{x}+{y}")

        self._build_ui()

    def _set_icon(self):
        """设置窗口图标"""
        try:
            # 尝试加载ICO图标
            if hasattr(sys, '_MEIPASS'):
                # PyInstaller打包后的路径
                ico_path = os.path.join(sys._MEIPASS, "LOGO.ico")
            else:
                ico_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "LOGO.ico")

            if os.path.exists(ico_path):
                self.root.iconbitmap(ico_path)
        except Exception:
            pass  # 图标加载失败不影响程序运行

    def _build_ui(self):
        """构建界面"""
        main_frame = tk.Frame(self.root, bg=self.BG_COLOR, padx=30, pady=20)
        main_frame.pack(fill=tk.BOTH, expand=True)

        # ---- 交易账号 ----
        lbl_account = tk.Label(
            main_frame, text="交易账号", bg=self.BG_COLOR,
            fg=self.LABEL_FG, font=("Microsoft YaHei UI", 11)
        )
        lbl_account.pack(anchor=tk.W, pady=(0, 4))

        self.entry_account = tk.Entry(
            main_frame, bg=self.INPUT_BG, fg=self.INPUT_FG,
            insertbackground=self.INPUT_FG,
            font=("Consolas", 13), relief=tk.FLAT,
            highlightthickness=1, highlightcolor="#506080",
            highlightbackground=self.BORDER_COLOR
        )
        self.entry_account.pack(fill=tk.X, ipady=6, pady=(0, 12))
        self.entry_account.bind("<KeyRelease>", self._on_input_change)

        # ---- 有效期至 ----
        lbl_expiry = tk.Label(
            main_frame, text="有效期至", bg=self.BG_COLOR,
            fg=self.LABEL_FG, font=("Microsoft YaHei UI", 11)
        )
        lbl_expiry.pack(anchor=tk.W, pady=(0, 4))

        # 默认日期：当前日期+30天
        default_date = (datetime.date.today() + datetime.timedelta(days=30)).strftime("%Y-%m-%d")
        self.entry_expiry = tk.Entry(
            main_frame, bg=self.INPUT_BG, fg=self.INPUT_FG,
            insertbackground=self.INPUT_FG,
            font=("Consolas", 13), relief=tk.FLAT,
            highlightthickness=1, highlightcolor="#506080",
            highlightbackground=self.BORDER_COLOR
        )
        self.entry_expiry.pack(fill=tk.X, ipady=6, pady=(0, 12))
        self.entry_expiry.insert(0, default_date)
        self.entry_expiry.bind("<KeyRelease>", self._on_input_change)

        # ---- 信息标签 ----
        self.lbl_info = tk.Label(
            main_frame, text="", bg=self.BG_COLOR,
            fg=self.INFO_FG, font=("Microsoft YaHei UI", 10)
        )
        self.lbl_info.pack(anchor=tk.W, pady=(0, 12))
        self._on_input_change()  # 初始化信息

        # ---- 生成授权码按钮 ----
        self.btn_generate = tk.Button(
            main_frame, text="生成授权码",
            bg=self.BTN_GOLD_BG, fg=self.BTN_GOLD_FG,
            activebackground="#d4b438", activeforeground=self.BTN_GOLD_FG,
            font=("Microsoft YaHei UI", 12, "bold"),
            relief=tk.FLAT, cursor="hand2",
            command=self._on_generate
        )
        self.btn_generate.pack(fill=tk.X, ipady=8, pady=(0, 12))

        # ---- 授权码显示框 ----
        self.entry_license = tk.Entry(
            main_frame, bg=self.INPUT_BG, fg=self.INPUT_FG,
            font=("Consolas", 12), relief=tk.FLAT,
            readonlybackground=self.INPUT_BG,
            highlightthickness=1, highlightcolor=self.BORDER_COLOR,
            highlightbackground=self.BORDER_COLOR,
            state="readonly"
        )
        self.entry_license.pack(fill=tk.X, ipady=8, pady=(0, 12))

        # ---- 复制到剪贴板按钮 ----
        self.btn_copy = tk.Button(
            main_frame, text="复制到剪贴板",
            bg=self.BTN_BLUE_BG, fg=self.BTN_BLUE_FG,
            activebackground="#4080d0", activeforeground=self.BTN_BLUE_FG,
            font=("Microsoft YaHei UI", 11),
            relief=tk.FLAT, cursor="hand2",
            command=self._on_copy,
            state=tk.DISABLED
        )
        self.btn_copy.pack(fill=tk.X, ipady=6, pady=(0, 10))

        # ---- 状态标签 ----
        self.lbl_status = tk.Label(
            main_frame, text="", bg=self.BG_COLOR,
            fg=self.STATUS_FG, font=("Microsoft YaHei UI", 9)
        )
        self.lbl_status.pack(anchor=tk.W)

    def _on_input_change(self, event=None):
        """输入变化时更新信息标签"""
        account = self.entry_account.get().strip()
        expiry = self.entry_expiry.get().strip()

        if account and expiry:
            self.lbl_info.config(text=f"账号: {account} | 有效期至: {expiry}")
        elif account:
            self.lbl_info.config(text=f"账号: {account}")
        elif expiry:
            self.lbl_info.config(text=f"有效期至: {expiry}")
        else:
            self.lbl_info.config(text="")

    def _validate_inputs(self) -> tuple:
        """验证输入，返回 (account, expiry_yyyymmdd) 或 (None, None)"""
        account = self.entry_account.get().strip()
        expiry_str = self.entry_expiry.get().strip()

        # 验证账号：纯数字
        if not account:
            self._show_error("请输入交易账号")
            return None, None
        if not account.isdigit():
            self._show_error("交易账号必须为纯数字")
            return None, None

        # 验证日期格式 YYYY-MM-DD
        if not expiry_str:
            self._show_error("请输入有效期")
            return None, None

        try:
            expiry_date = datetime.datetime.strptime(expiry_str, "%Y-%m-%d").date()
        except ValueError:
            self._show_error("日期格式错误，请使用 YYYY-MM-DD")
            return None, None

        # 检查日期不能早于今天
        if expiry_date < datetime.date.today():
            self._show_error("有效期不能早于今天")
            return None, None

        expiry_yyyymmdd = expiry_date.strftime("%Y%m%d")
        return account, expiry_yyyymmdd

    def _show_error(self, msg: str):
        """显示错误信息"""
        self.lbl_status.config(text=msg, fg=self.ERROR_FG)
        # 3秒后恢复状态标签颜色
        self.root.after(3000, lambda: self.lbl_status.config(fg=self.STATUS_FG))

    def _on_generate(self):
        """生成授权码"""
        account, expiry_yyyymmdd = self._validate_inputs()
        if account is None:
            return

        license_key = generate_license(account, expiry_yyyymmdd)

        self.entry_license.config(state=tk.NORMAL)
        self.entry_license.delete(0, tk.END)
        self.entry_license.insert(0, license_key)
        self.entry_license.config(state="readonly")

        self.btn_copy.config(state=tk.NORMAL)
        self.lbl_status.config(text="授权码已生成", fg=self.STATUS_FG)

    def _on_copy(self):
        """复制授权码到剪贴板"""
        license_key = self.entry_license.get()
        if not license_key:
            return

        try:
            self.root.clipboard_clear()
            self.root.clipboard_append(license_key)
            self.lbl_status.config(text="已复制到剪贴板", fg=self.STATUS_FG)
        except Exception as e:
            self.lbl_status.config(text=f"复制失败: {e}", fg=self.ERROR_FG)


# ========== 入口 ==========

def main():
    root = tk.Tk()
    app = LicenseGeneratorApp(root)
    root.mainloop()


if __name__ == "__main__":
    main()
