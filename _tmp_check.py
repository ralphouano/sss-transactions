import re
for path in [
    r"d:\sss\sss-transactions\resources\js\Layouts\PublicTransactionLayout.vue",
    r"d:\sss\sss-transactions\resources\js\Layouts\AuthenticatedLayout.vue",
]:
    t = open(path, encoding="utf-8").read()
    m = re.search(r'class="(...)-nav-gradient', t)
    print(path.split("\\")[-1], [ord(c) for c in m.group(1)], m.group(1))
