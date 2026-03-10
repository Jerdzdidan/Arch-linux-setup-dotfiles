-- init.lua

-- Detect .blade.php files as blade filetype
vim.filetype.add({
  extension = {
    blade = 'blade',
  },
  pattern = {
    ['.*%.blade%.php'] = 'blade',
  },
})

-- Set Blade-specific settings
vim.api.nvim_create_autocmd("FileType", {
  pattern = "blade",
  callback = function()
    vim.bo.commentstring = '{{-- %s --}}'
    vim.bo.tabstop = 4
    vim.bo.shiftwidth = 4
    vim.bo.softtabstop = 4
    vim.bo.expandtab = true
  end,
})
