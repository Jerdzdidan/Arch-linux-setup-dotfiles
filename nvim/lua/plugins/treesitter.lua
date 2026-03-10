return {
  {
    "nvim-treesitter/nvim-treesitter",
    opts = {
      ensure_installed = {
        "php",
        "blade",
        "html",
        "css",
        "javascript",
        "phpdoc",
      },
      indent = {
        enable = true,
        disable = { "blade" },   -- This tells LazyVim to skip Blade
      },
    },
  },
}
