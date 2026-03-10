return {
  {
    "ricardoramirezr/blade-nav.nvim",
    dependencies = {
      "hrsh7th/nvim-cmp",
    },
    ft = { "blade", "php" },
  },
  {
    "nvim-treesitter/nvim-treesitter",
    opts = function(_, opts)
      vim.filetype.add({
        pattern = {
          [".*%.blade%.php"] = "blade",
        },
      })

      if type(opts.ensure_installed) == "table" then
        vim.list_extend(opts.ensure_installed, { "html", "php", "phpdoc" })
      end
    end,
  },
}
