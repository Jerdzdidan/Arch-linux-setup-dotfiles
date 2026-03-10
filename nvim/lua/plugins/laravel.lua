return {
  {
    "adibhanna/laravel.nvim",
    dependencies = {
      "MunifTanjim/nui.nvim",
      "nvim-lua/plenary.nvim",
    },
    keys = {
      { "<leader>la", ":Artisan<cr>",           desc = "Laravel Artisan" },
      { "<leader>lc", ":Composer<cr>",          desc = "Composer" },
      { "<leader>lr", ":LaravelRoute<cr>",      desc = "Laravel Routes" },
      { "<leader>lm", ":LaravelMake<cr>",       desc = "Laravel Make" },
      { "<leader>lf", ":LaravelController<cr>", desc = "Laravel Controllers" },
      { "<leader>ld", ":LaravelModel<cr>",      desc = "Laravel Models" },
    },
    config = function()
      require("laravel").setup()
    end,
  },
  -- ensure intelephense installed
  {
    "mason-org/mason-lspconfig.nvim",
    opts = {
      ensure_installed = {
        "intelephense",
      },
    },
  },


  {
    "stevanmilic/nvim-lspimport",
    keys = {
      {
        "<leader>li",
        function()
          require("lspimport").import()
        end,
        desc = "Import class",
      },
    },
  },
}
