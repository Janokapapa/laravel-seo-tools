<?php

namespace JanDev\SeoTools\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AddSeoFieldsCommand extends Command
{
    protected $signature = 'seo:add-fields {table}';

    protected $description = 'Generate a migration to add SEO fields to an existing table';

    public function handle(): int
    {
        $table = $this->argument('table');
        $className = 'AddSeoFieldsTo' . Str::studly($table) . 'Table';
        $fileName = date('Y_m_d_His') . '_add_seo_fields_to_' . $table . '_table.php';

        $stub = $this->getStub($table, $className);

        $path = database_path("migrations/{$fileName}");
        file_put_contents($path, $stub);

        $this->info("Migration created: {$path}");

        return self::SUCCESS;
    }

    protected function getStub(string $table, string $className): string
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('{$table}', function (Blueprint \$table) {
            \$table->string('meta_title')->nullable();
            \$table->text('meta_description')->nullable();
            \$table->string('meta_keywords')->nullable();
            \$table->string('og_image')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('{$table}', function (Blueprint \$table) {
            \$table->dropColumn(['meta_title', 'meta_description', 'meta_keywords', 'og_image']);
        });
    }
};
PHP;
    }
}
